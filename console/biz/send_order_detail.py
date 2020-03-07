from libs.helper import *
from biz.send_email import *
import time
class OrderGoodsDetail:
    def __init__(self):
        self.db_helper = DBHelper()
        self.default_config = ConfigHelper.getDefault()
        self.sendMail = SendMail()
    def execute_to(self):
        now = time.strftime("%Y-%m-%d", time.localtime())
        outfilePath = self.default_config['toexcel']['goods_detail_path']
        file_list = []
        totalName = '' # outfilePath + "/" + now + "goods_detail_total.xlsx"
        morningName = outfilePath + "/" + now + "goods_detail_morning.xlsx"
        afternoonName = '' # outfilePath + "/" + now + "goods_detail_afternoon.xlsx"
        if os.path.exists(totalName):
            if (os.path.getsize(totalName) > 0):
                file_list.append(totalName)

        if os.path.exists(morningName):
            if (os.path.getsize(morningName) > 0):
                file_list.append(morningName)

        if os.path.exists(afternoonName):
            if (os.path.getsize(afternoonName) > 0):
                file_list.append(afternoonName)

        subject = now+'采购单品统计'
        content_text = ''
        attachments = file_list
       
        cc = ['linyifan@cook2ez.com']
        receivers = ['cykj@cook2ez.com','linlihong@cook2ez.com']
  
        # receivers = ['linyifan@cook2ez.com', '159496496@qq.com', 'linlihong@cook2ez.com','411395201@qq.com','shiyiting@cook2ez.com','limingqi@cook2ez.com']
        self.sendMail.send_email_to(subject, content_text, attachments, receivers, cc)
