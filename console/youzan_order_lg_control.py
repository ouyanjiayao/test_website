from biz.youzan_order_lg_control import *
import time

class OrderPrintThread(threading.Thread):

    def __init__(self,type,offline_id):
        self.order_print = OrderPrint(type,offline_id)
        threading.Thread.__init__(self)

    def run(self):
        while (1):
            try:
                self.order_print.execute(50)
            except Exception as e:
                youzan_order_control_logger.exception(e)
            time.sleep(5)

print('start youzan order lg  control')
order_lg_zt_print_thread = OrderPrintThread(1,'lg')
order_lg_zt_print_thread.start()
order_lg_fk_print_thread = OrderPrintThread(2,'lg')
order_lg_fk_print_thread.start()
print('start youzan order hr  control')
order_hr_zt_print_thread = OrderPrintThread(1,'hr')
order_hr_zt_print_thread.start()
order_hr_fk_print_thread = OrderPrintThread(2,'hr')
order_hr_fk_print_thread.start()
print('start youzan order st  control')
order_st_zt_print_thread = OrderPrintThread(1,'st')
order_st_zt_print_thread.start()
order_st_fk_print_thread = OrderPrintThread(2,'st')
order_st_fk_print_thread.start()