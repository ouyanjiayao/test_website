import zmail
class SendMail:
    def __init__(self):
        self.mail_content = None
    def send_email_to(self,subject,content_text,attachments,receivers,cc):
        mail_content = {
            'subject': subject,
            'content_text': content_text,
            'attachments': attachments
        }

        server = zmail.server('cook2ez@163.com', 'Cykj20181212')
        # server = zmail.server('490247848@qq.com', 'CYKJ20181212')
        server.send_mail(receivers, mail_content, cc)
        print('send success')
