from libs.helper import *
from libs.youzan import ApiClient
import json
import time
import os
class GetGoodsGroupSyn:

    def __init__(self):
        self.db_helper = DBHelper()
        self.youzan_api = ApiClient()
        self.default_config = ConfigHelper.getDefault()
        self.goods_group_path = self.default_config['goods_group']['goods_group_path']
    def execute_to(self):
        try:
            api_name = 'youzan.itemcategories.tags.get'
            response = self.youzan_api.invoke(api_name, '3.0.0', {

            })
            res = json.dumps(response)
            path = self.goods_group_path

            with open(path, 'w+') as file_object:
                file_object.write(res)

        except Exception as e:
            youzan_tags_logger.exception(e)

