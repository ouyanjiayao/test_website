import redis
import pymysql
import configparser
import logging
import os

class ConfigHelper:

    default_config = None

    @staticmethod
    def getDefault():
        config = ConfigHelper.default_config
        if not config:
            config = configparser.ConfigParser()
            config.read('configs/default.ini', encoding="utf-8")
            ConfigHelper.default_config = config
        return config

class RedisHelper:

    def __init__(self):
        self.conn = None
        self.default_config = ConfigHelper.getDefault()

    def getConnect(self):
        conn = self.conn
        if not conn:
            conn = redis.Redis(self.default_config['redis']['host'],int(self.default_config['redis']['port']))
            self.conn = conn
        return conn

class DBHelper:

    def __init__(self):
        self.conn = None
        self.default_config = ConfigHelper.getDefault()

    def getConnect(self):
        conn = self.conn
        if not conn:
            conn = pymysql.connect(host=self.default_config['db']['host'], user=self.default_config['db']['user'], passwd=self.default_config['db']['passwd'], db=self.default_config['db']['db'], charset=self.default_config['db']['charset'],port=int(self.default_config['db']['port']))
            self.conn = conn
        else:
            conn.ping()
        return conn


def create_logger(file):
    log = logging.getLogger("logger")
    log.setLevel(logging.INFO)
    log_handler = logging.FileHandler(filename=file,encoding='utf-8' )
    log_handler.setLevel(logging.INFO)
    formats = logging.Formatter('%(asctime)s %(levelname)s: %(message)s',
                                datefmt='[%Y/%m/%d %I:%M:%S]')
    log_handler.setFormatter(formats)
    log.addHandler(log_handler)
    return log

youzan_syn_logger = create_logger('logs/youzan_syn.log')
youzan_order_control_logger = create_logger('logs/youzan_order_control.log')
youzan_tags_logger = create_logger('logs/youzan_tags.log')
order_detail_logger = create_logger('logs/order_detail.log')
order_goods_detail_logger = create_logger('logs/order_goods_detail.log')

def log_exception(e):
    youzan_syn_logger.exception(e)