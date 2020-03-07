from libs.helper import *
from libs.youzan import ApiClient
from libs.helper import ConfigHelper
import json
import time

class UploadImageSyn:

    def __init__(self):
        self.db_helper = DBHelper()
        self.youzan_api = ApiClient()
        self.default_config = ConfigHelper.getDefault()

    def execute_to(self,limit):
        conn = self.db_helper.getConnect()
        cursor = conn.cursor(cursor=pymysql.cursors.DictCursor)
        cursor.execute('select * from tbl_upload_file where is_delete = 0 and (youzan_version is null or youzan_version!=version)  order by id asc limit %s',(limit))
        rows = cursor.fetchall()
        updates = []
        error_updates = []
        log_inserts = []
        if rows:
            for row in rows:
                api_name = None
                syn_state = 1
                response = None
                try:
                    image_url = row['url'].replace('@web', self.default_config['web']['upload_dir'])
                    if not row['youzan_id']:
                        youzan_id = None
                        files = {'image[]': open(image_url, 'rb')}
                        api_name = 'youzan.materials.storage.platform.img.upload'
                        response = self.youzan_api.invoke(api_name, '3.0.0',files=files)
                        if not (response['code'] == 200):
                            syn_state = 2
                        else:
                            youzan_id = response['data']['image_id']
                            syn_state = 3
                        updates.append((youzan_id, row['version'],time.time(),syn_state, row['id']))
                except Exception as e:
                    youzan_syn_logger.exception(e)
                if syn_state == 1:
                    error_updates.append((time.time(),syn_state, row['id']))
                if response:
                    response_content = json.dumps(response)
                else:
                    response_content = '接口调用失败'
                log_inserts.append((response_content,time.time(),3,row['id'],syn_state,api_name))
                time.sleep(1)
            if len(updates) > 0:
                cursor.executemany("update tbl_upload_file set youzan_id = %s,youzan_version = %s,youzan_syn_time = %s,youzan_syn_state = %s where id = %s",updates)
            if len(error_updates) > 0:
                cursor.executemany("update tbl_upload_file set youzan_syn_time = %s, youzan_syn_state = %s where id = %s",error_updates)
            if len(log_inserts) > 0:
                cursor.executemany("insert into tbl_youzan_syn_log(response_content,created_time,type,syn_id,syn_state,api_name) values(%s,%s,%s,%s,%s,%s)",log_inserts)
            cursor.close()


    def execute_delete(self):
        conn = self.db_helper.getConnect()
        cursor = conn.cursor(cursor=pymysql.cursors.DictCursor)
        cursor.execute('delete from tbl_upload_file where is_delete = 1')
        cursor.close()