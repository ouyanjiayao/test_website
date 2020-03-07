from biz.order_detail_excel import *
import threading

class OrderGoodsDetailThread():
    def __init__(self):
        self.order_detail_excel = OrderGoodsDetail()
        threading.Thread.__init__(self)
    def runTotal(self):
        try:
            type ='total'
            self.order_detail_excel.execute_to(type)
        except Exception as e:
            youzan_syn_logger.exception(e)

    def runMorning(self):
        try:
            type ='morning'
            self.order_detail_excel.execute_to(type)
        except Exception as e:
            youzan_syn_logger.exception(e)

    def runAfternoon(self):
        try:
            type ='afternoon'
            self.order_detail_excel.execute_to(type)
        except Exception as e:
            youzan_syn_logger.exception(e)

order_goods_detail_thread = OrderGoodsDetailThread()
order_goods_detail_thread.runMorning()
print('start order_goods_detail to excel')