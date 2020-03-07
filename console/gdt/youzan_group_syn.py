from libs.helper import *
from libs.youzan import ApiClient
import json
import time
import os
import re
class YouzanGroupSyn:

    def __init__(self):
        self.db_helper = DBHelper()
        self.youzan_api = ApiClient()
        self.default_config = ConfigHelper.getDefault()
        self.goods_group_path = self.default_config['goods_group']['goods_group_path']
        self.goods_path = self.default_config['goods_group']['goods_path']
        self.group_file_name = self.default_config['goods_group']['group_file_name']
    def execute_to(self, page):
        try:
            conn = self.db_helper.getConnect()
            cursor = conn.cursor(cursor=pymysql.cursors.DictCursor)
            path = self.goods_group_path
            gd_path = self.goods_path

            file_path = self.goods_path+'/'+self.group_file_name
            if not os.path.exists(file_path):
                os.mkdir(file_path)

            file_content = ''
            with open(path, 'r+') as file_object:
                file_content = file_object.read()
            file_content = json.loads(file_content)
            data_tags = file_content['data']['tags']

            # api_name = 'youzan.showcase.render.api.listGoodsByTagId'
            tags_inserts = []
            for i, tags in enumerate(data_tags):
                if self.filter_tg(data_tags[i]['name']):
                    # self.setListGoodsByTagId(api_name, page, data_tags[i], file_path)
                    tags_inserts.append((data_tags[i]['name'], 2, data_tags[i]['id'], 0, 0, 1, 1, time.time(), 3))
            cursor.executemany(
                "insert into tbl_goods_tag(name, type, youzan_id, system_create_id,is_delete,version,youzan_version,youzan_syn_time,youzan_syn_state) values(%s,%s,%s,%s,%s,%s,%s,%s,%s)",
                tags_inserts)
            cursor.close()
            exit(0)
        except Exception as e:
            youzan_tags_logger.exception(e)

    # def setListGoodsByTagId(self, api_name, page, data_tags, gd_path):
    #
    #     response = self.youzan_api.invoke(api_name, '1.0.0', {
    #         'tag_id': data_tags['id'],
    #         'page': page,
    #         'page_size': 100
    #     })
    #     data_tags['name'] = self.filter_name(data_tags['name'])
    #     out_path = gd_path + '/'+data_tags['name']+'_'+str(page)+'.json'
    #     if response['data']['list']:
    #         res = json.dumps(response)
    #         with open(out_path, 'w+') as file_object:
    #             file_object.write(res)
    #
    #     if response['data']['has_more']:
    #         self.getListGoodsByTagId(api_name, page + 1, data_tags, gd_path)


    # def filter_name(self, filter_name):
    #     re_name = filter_name.replace('/', '').replace(':', '').replace('*', '').replace('?', '').replace('"', '').replace('<', '').replace('>', '').replace('|', '')
    #     return re_name

    def filter_tg(self, tg_str):
        up_str = str.upper(tg_str)
        is_tg = re.findall(r'TG(.+?)', up_str)
        if not is_tg:
            return False
        return True
