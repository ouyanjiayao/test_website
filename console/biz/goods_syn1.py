from libs.helper import *
from libs.youzan import ApiClient
import json
import time


def get_tc_config_price(cursor,goods_id,sku_detail_id,attr_config):
    cost = 0
    cursor.execute('select * from tbl_goods_tc_config where goods_id = %s and sku_detail_id = %s order by id asc',
                   (goods_id, sku_detail_id))
    s_tc_configs = cursor.fetchall()
    for s_tc_config in s_tc_configs:
        cp_config_content = json.loads(s_tc_config['content'])
        if cp_config_content['attr']:
            s_sku_id = []
            for cp_config_content_attr in cp_config_content['attr']:
                cursor.execute('select * from tbl_goods_attr where id = %s',
                               (cp_config_content_attr['attr_id']))
                attr_t = cursor.fetchone()
                if attr_t and attr_t['type'] == 1:
                    s_sku_id.append(
                        cp_config_content_attr['attr_id'] + '_' + cp_config_content_attr['item_id'])
            s_sku_id = ':'.join(s_sku_id)
            cursor.execute('select * from tbl_goods_sku_detail where goods_id = %s and sku_id = %s',(s_tc_config['dp_goods_id'],s_sku_id))
            s_sku_detail = cursor.fetchone()
            cursor.execute('select * from tbl_goods_attr_config where goods_id = %s',(s_tc_config['dp_goods_id']))
            s_attr_config = cursor.fetchone()
            s_price = get_cp_config_price(cursor,s_tc_config['dp_goods_id'],s_sku_detail['id'],s_attr_config)
            cost += s_price['cost']
    return {'cost': cost, 'sale_price': cost * float(attr_config['sale_scale'])}


def get_cp_config_price(cursor,goods_id,sku_detail_id,attr_config):
    cost = 0
    cursor.execute('select * from tbl_goods_cp_config where goods_id = %s and sku_detail_id = %s order by id asc',
                   (goods_id, sku_detail_id))
    cp_config_rows = cursor.fetchall()
    for cp_config_row in cp_config_rows:
        cp_config_content = json.loads(cp_config_row['content'])
        cursor.execute('select * from tbl_goods_attr_config where goods_id = %s', (cp_config_row['dp_goods_id']))
        cp_config_row_attr_config = cursor.fetchone()
        d_cost = 0
        if cp_config_content['attr']:
            s_sku_id = []
            for cp_config_content_attr in cp_config_content['attr']:
                cursor.execute('select * from tbl_goods_attr where id = %s', (cp_config_content_attr['attr_id']))
                attr_t = cursor.fetchone()
                if attr_t and attr_t['type'] == 1:
                    s_sku_id.append(cp_config_content_attr['attr_id'] + '_' + cp_config_content_attr['item_id'])
            s_sku_id = ':'.join(s_sku_id)
            cursor.execute('select * from tbl_goods_sku_price where goods_id = %s and sku_id = %s',
                           (cp_config_row['dp_goods_id'], s_sku_id))
            s_sku_price = cursor.fetchone()
            if s_sku_price:
                d_cost = float(s_sku_price['cost'])
        else:
            d_cost = float(cp_config_row_attr_config['cost'])
        if cp_config_row_attr_config['price_count_type'] == 1:
            cost += d_cost * float(cp_config_content['unit'])
        elif cp_config_row_attr_config['price_count_type'] == 2:
            cost += (d_cost / 500) * float(cp_config_content['unit'])
    return {'cost':cost,'sale_price': cost * float(attr_config['sale_scale'])}

class GoodsSyn:

    def __init__(self):
        self.db_helper = DBHelper()
        self.youzan_api = ApiClient()
        self.cursor = None

    def parse_sale_price(self,price):
        return  round(round(price,2)*100)

    def parse_image_ids(self,ids):
        return ",".join('%s' % id for id in ids)

    def parse_tag_ids(self,ids):
        return ','.join('%s' % id for id in ids)

    def get_goods_title(self,name,adorn_text):
        return name + ''+ adorn_text

    def get_sku_detail(self,goods_id,attr_config,is_update,type):
        sku_stocks = []
        item_sku_extends = []
        cursor = self.cursor
        cursor.execute('select * from tbl_goods_sku_detail where goods_id = %s order by id asc',(goods_id))
        rows = cursor.fetchall()
        for row in rows:
            if type == 2:
                config_price = get_cp_config_price(cursor,goods_id,row['id'],attr_config)
                row['cost'] = config_price['cost']
                if attr_config['auto_create_sale_price'] == 1:
                    row['sale_price']  = config_price['sale_price']
            if type == 3:
                config_price = get_tc_config_price(cursor, goods_id, row['id'], attr_config)
                row['cost'] = config_price['cost']
                if attr_config['auto_create_sale_price'] == 1:
                    row['sale_price'] = config_price['sale_price']
            sku_items = []
            sku_id_splits = row['sku_id'].split(':')
            item_sku_extend = {'cost_price':self.parse_sale_price(row['cost']),'s1':0,'s2':0,'s3':0,'s4':0,'s5':0}
            i = 0
            for sku_id_split in sku_id_splits:
                sku_id_split = sku_id_split.split('_')
                attr_id = sku_id_split[0]
                item_id = sku_id_split[1]
                item_sku_extend['s'+str(i+1)] = item_id
                cursor.execute('select * from tbl_goods_attr where id = %s', (attr_id))
                attr_row = cursor.fetchone()
                cursor.execute('select * from tbl_goods_attr_item where id = %s', (item_id))
                item_row = cursor.fetchone()
                sku_items.append({'k':attr_row['name'],'kid':attr_id,'v':item_row['name'],'vid':item_id})
                i += 1
            sale_price = row['sale_price']
            if sale_price <=0 :
                sale_price = 0.01
            item = {'price':self.parse_sale_price(sale_price),'skus':sku_items,'item_no':row['sku_id']}
            #if not is_update:
            item['quantity'] = row['stock']
            sku_stocks.append(item)
            item_sku_extends.append(item_sku_extend)
        if len(item_sku_extends) <= 0:
            item_sku_extend = {'cost_price': self.parse_sale_price(attr_config['cost']), 's1': 0, 's2': 0, 's3': 0, 's4': 0, 's5': 0}
            item_sku_extends.append(item_sku_extend)
        return {
            'sku_stocks':sku_stocks,
            'item_sku_extends':item_sku_extends
        }

    def get_image_ids(self,images):
        if not images:
            return []
        ids = []
        images = json.loads(images)
        cursor = self.cursor
        i = 0
        try_count = 0
        while i<len(images):
            cursor.execute('select id,youzan_id,youzan_syn_state from tbl_upload_file where id = %s', (images[i]['id']))
            image_row = cursor.fetchone()
            if try_count > 10:
                i += 1
                try_count = 0
            if image_row and image_row['youzan_id']:
                ids.append(image_row['youzan_id'])
                i += 1
                try_count = 0
            else:
                time.sleep(2)
                try_count += 1
        return ids

    def get_tag_ids(self,goods_id):
        ids = []
        cursor = self.cursor
        cursor.execute('select tag_id from tbl_goods_tag_assign  where goods_id = %s',(goods_id))
        tag_assign_rows = cursor.fetchall()
        i = 0
        try_count = 0
        while i < len(tag_assign_rows):
            cursor.execute('select youzan_id from tbl_goods_tag where id = %s',(tag_assign_rows[i]['tag_id']))
            tag_row = cursor.fetchone()
            if try_count > 10:
                i += 1
                try_count = 0
            if tag_row['youzan_id']:
                ids.append(tag_row['youzan_id'])
                i += 1
                try_count = 0
            else:
                time.sleep(2)
                try_count += 1

        cursor.execute('select category_id from tbl_goods_category_assign where goods_id = %s',(goods_id))
        category_assign_rows = cursor.fetchall()
        i = 0
        try_count = 0
        while i < len(category_assign_rows):
            cursor.execute('select youzan_id from tbl_goods_tag where system_create_id = %s', (category_assign_rows[i]['category_id']))
            tag_row = cursor.fetchone()
            if try_count > 10:
                i += 1
                try_count = 0
            if tag_row['youzan_id']:
                ids.append(tag_row['youzan_id'])
                i += 1
                try_count = 0
            else:
                time.sleep(2)
                try_count += 1
        return ids


    def execute_to(self,limit):
        conn = self.db_helper.getConnect()
        cursor = conn.cursor(cursor=pymysql.cursors.DictCursor)
        self.cursor = cursor
        cursor.execute('select * from tbl_goods where is_delete = 0 and (youzan_version is null or youzan_version!=version) order by id asc limit %s',(limit))
        rows = cursor.fetchall()
        updates = []
        error_updates = []
        log_inserts = []
        for row in rows:
            api_name = None
            syn_state = 1
            response = None
            try:
                cursor.execute('select * from tbl_goods_attr_config where goods_id = %s', (row['id']))
                attr_config = cursor.fetchone()
                youzan_id = row['youzan_id']
                image_ids = self.get_image_ids(row['images'])
                tag_ids = self.get_tag_ids(row['id'])
                sale_price = attr_config['sale_price']
                if not sale_price or sale_price<=0:
                    sale_price = 0.01
                if row['youzan_id']:
                    sku_detail = self.get_sku_detail(row['id'], attr_config,True,row['type'])
                    params = {
                        'item_id': row['youzan_id'],
                        'item_no': str(row['id']),
                        'title': self.get_goods_title(row['name'],row['adorn_text']),
                        'image_ids': self.parse_image_ids(image_ids),
                        'desc': ' ',
                        'price': self.parse_sale_price(sale_price),
                        'tag_ids': self.parse_tag_ids(tag_ids),
                        'is_display': row['state'],
                        'remove_image_ids': row['youzan_image_ids'],
                        'sku_stocks':json.dumps(sku_detail['sku_stocks']),
                        'item_sku_extends':json.dumps(sku_detail['item_sku_extends']),
                        'sell_point':row['youzan_sell_point'],
                        'origin_price':row['youzan_origin_price'],
                        'join_level_discount':row['youzan_join_level_discount']
                    }
                    api_name = 'youzan.item.update'
                    response = self.youzan_api.invoke(api_name, '3.0.1',params)
                    if response['code'] == 200:
                        syn_state = 3
                    else:
                        syn_state = 2
                else:
                    sku_detail = self.get_sku_detail(row['id'], attr_config,False,row['type'])
                    params = {
                        'item_no': str(row['id']),
                        'item_type':0,
                        'cid': 3000000,
                        'title': self.get_goods_title(row['name'],row['adorn_text']),
                        'image_ids': self.parse_image_ids(image_ids),
                        'desc': ' ',
                        'price': self.parse_sale_price(sale_price),
                        'quantity': attr_config['stock'],
                        'hide_stock': 1,
                        'tag_ids': self.parse_tag_ids(tag_ids),
                        'join_level_discount': 1,
                        'is_display': row['state'],
                        'sku_stocks': json.dumps(sku_detail['sku_stocks']),
                        'item_sku_extends': json.dumps(sku_detail['item_sku_extends']),
                        'sell_point': row['youzan_sell_point'],
                        'origin_price': row['youzan_origin_price'],
                        'join_level_discount': row['youzan_join_level_discount'],
                        'delivery_template_id': '732192'
                    }
                    api_name = 'youzan.item.create'
                    response = self.youzan_api.invoke(api_name, '3.0.1', params)
                    if response['code'] == 200:
                        youzan_id = response['data']['item']['item_id']
                        syn_state = 3
                    else:
                        syn_state = 2
                updates.append((youzan_id,params['image_ids'], row['version'],time.time(),syn_state, row['id']))
            except Exception as e:
                youzan_syn_logger.exception(e)
            if syn_state == 1:
                error_updates.append((time.time(),syn_state, row['id']))
            if response:
                response_content = json.dumps(response,ensure_ascii=False)
            else:
                response_content = '接口调用失败'
            log_inserts.append((response_content,time.time(),1,row['id'],syn_state,api_name))
            time.sleep(1)
        if len(updates) > 0:
            cursor.executemany("update tbl_goods set youzan_id = %s,youzan_image_ids = %s,youzan_version = %s,youzan_syn_time = %s,youzan_syn_state = %s where id = %s",updates)
        if len(error_updates) > 0:
            cursor.executemany("update tbl_goods set youzan_syn_time = %s, youzan_syn_state = %s where id = %s",error_updates)
        if len(log_inserts) > 0:
            cursor.executemany("insert into tbl_youzan_syn_log(response_content,created_time,type,syn_id,syn_state,api_name) values(%s,%s,%s,%s,%s,%s)",log_inserts)
        cursor.close()

    def execute_delete(self,limit):
        conn = self.db_helper.getConnect()
        cursor = conn.cursor(cursor=pymysql.cursors.DictCursor)
        cursor.execute('select id,youzan_id from tbl_goods where is_delete = 1 order by id asc limit %s',(limit))
        rows = cursor.fetchall()
        deletes = []
        log_inserts = []
        for row in rows:
            api_name = None
            response = None
            syn_state = 1
            try:
                if row['youzan_id']:
                    api_name = 'youzan.item.delete'
                    response = self.youzan_api.invoke(api_name, '3.0.0', {
                        'item_id': row['youzan_id']
                    })
                    if response['code'] == 200:
                        deletes.append((row['id']))
                        syn_state = 3
                    else:
                        syn_state = 2
                else:
                    deletes.append((row['id']))
            except Exception as e:
                youzan_syn_logger.exception(e)
            if response:
                response_content = json.dumps(response,ensure_ascii=False)
            else:
                response_content = '接口调用失败'
            log_inserts.append((response_content, time.time(), 1, row['id'], syn_state, api_name))
        cursor.executemany('delete from tbl_goods where id = %s', deletes)
        if len(log_inserts) > 0:
            cursor.executemany("insert into tbl_youzan_syn_log(response_content,created_time,type,syn_id,syn_state,api_name) values(%s,%s,%s,%s,%s,%s)",log_inserts)
        cursor.close()