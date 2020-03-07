from libs.helper import *
from libs.youzan import ApiClient
import json
import time

class GoodsGroupSyn:

    def __init__(self):
        self.db_helper = DBHelper()
        self.youzan_api = ApiClient()

    def execute_to(self,limit):
        conn = self.db_helper.getConnect()
        cursor = conn.cursor(cursor=pymysql.cursors.DictCursor)
        cursor.execute('select * from tbl_goods_tag where is_delete = 0 and (youzan_version is null or youzan_version!=version) order by id asc limit %s',(limit))
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
                    youzan_id = row['youzan_id']
                    if not youzan_id:
                        api_name = 'youzan.itemcategories.tag.add'
                        response = self.youzan_api.invoke(api_name, '3.0.0', {
                            'name': row['name']
                        })
                        if not (response['code'] == 200):
                            syn_state = 2
                        else:
                            youzan_id = response['data']['tag']['id']
                            syn_state = 3
                    else:
                        api_name = 'youzan.itemcategories.tag.update'
                        response = self.youzan_api.invoke(api_name, '3.0.0', {
                            'name': row['name'],
                            'tag_id': youzan_id
                        })
                        if not (response['code'] == 200):
                            syn_state = 2
                        else:
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
                log_inserts.append((response_content,time.time(),2,row['id'],syn_state,api_name))
                time.sleep(100)
            if len(updates) > 0:
                cursor.executemany("update tbl_goods_tag set youzan_id = %s,youzan_version = %s,youzan_syn_time = %s,youzan_syn_state = %s where id = %s",updates)
            if len(error_updates) > 0:
                cursor.executemany("update tbl_goods_tag set youzan_syn_time = %s, youzan_syn_state = %s where id = %s",error_updates)
            if len(log_inserts) > 0:
                cursor.executemany("insert into tbl_youzan_syn_log(response_content,created_time,type,syn_id,syn_state,api_name) values(%s,%s,%s,%s,%s,%s)",log_inserts)
            cursor.close()


    def execute_delete(self,limit):
        conn = self.db_helper.getConnect()
        cursor = conn.cursor(cursor=pymysql.cursors.DictCursor)
        cursor.execute('select id,youzan_id from tbl_goods_tag where is_delete = 1 order by id asc limit %s',(limit))
        rows = cursor.fetchall()
        deletes = []
        log_inserts = []
        if rows:
            for row in rows:
                api_name = None
                response = None
                syn_state = 1
                try:
                    youzan_id = row['youzan_id']
                    if not youzan_id:
                        deletes.append((row['id']))
                    else:
                        api_name = 'youzan.itemcategories.tag.delete'
                        response = self.youzan_api.invoke(api_name, '3.0.0', {
                            'tag_id': youzan_id
                        })
                        if not (response['code'] == 200):
                            syn_state = 2
                        else:
                            deletes.append((row['id']))
                            syn_state = 3
                except Exception as e:
                    youzan_syn_logger.exception(e)
                if response:
                    response_content = json.dumps(response)
                else:
                    response_content = '接口调用失败'
                log_inserts.append((response_content, time.time(), 2, row['id'], syn_state, api_name))
            cursor.executemany('delete from tbl_goods_tag where id = %s', deletes)
            if len(log_inserts) > 0:
                cursor.executemany("insert into tbl_youzan_syn_log(response_content,created_time,type,syn_id,syn_state,api_name) values(%s,%s,%s,%s,%s,%s)",log_inserts)
            cursor.close()
