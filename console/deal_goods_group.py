from gdt.deal_goods_group import *
from libs.youzan import *
import threading

class DealGoodsGroupThread(threading.Thread):

    def __init__(self):
        self.deal_goods_group = DealGoodsGroupSyn()
        threading.Thread.__init__(self)

    def run(self, page):
        while (1):
            try:
                self.deal_goods_group.execute_to(page)

            except Exception as e:
                youzan_tags_logger.exception(e)
            time.sleep(5)

deal_goods_group_thread = DealGoodsGroupThread()
deal_goods_group_thread.run(1)
print('start deal goods group')