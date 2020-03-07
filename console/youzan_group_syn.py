from gdt.youzan_group_syn import *
from libs.youzan import *
import threading

class YouzanGroupSynThread(threading.Thread):

    def __init__(self):
        self.youzan_group_syn = YouzanGroupSyn()
        threading.Thread.__init__(self)

    def run(self, page):
        while (1):
            try:
                self.youzan_group_syn.execute_to(page)

            except Exception as e:
                youzan_tags_logger.exception(e)
            time.sleep(5)

deal_goods_group_thread = YouzanGroupSynThread()
deal_goods_group_thread.run(1)
print('start deal goods group')


