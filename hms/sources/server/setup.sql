# ================================================= #
# 系统初始化数据库
# ================================================= #

# ------------------------------------------------- #
# 建立数据库
# ------------------------------------------------- #
    # 删除同名数据库
    DROP DATABASE IF exists HMS;
    # 建立新的 Hotel Manage System 客房管理系统
    CREATE DATABASE HMS DEFAULT CHARACTER SET utf8 COLLATE=utf8_general_ci;
    USE HMS;

# ------------------------------------------------- #
# 创建表
# ------------------------------------------------- #
    # 用户表：保存系统用户信息
    CREATE TABLE admins(
        `username`        CHAR(20)          NOT NULL PRIMARY KEY                COMMENT '登录名',
        `password`        CHAR(64)          NOT NULL DEFAULT '',
        `name`            CHAR(20)          NOT NULL DEFAULT ''                 COMMENT '用户姓名',
        `sex`             ENUM('M','F')     NOT NULL DEFAULT 'M'                COMMENT 'Male男性，Female女性',
        `job`             CHAR(20)          NOT NULL DEFAULT '',
        `telephone`       CHAR(20)          NOT NULL DEFAULT '',
        `permissions`     BIT(8)            NOT NULL DEFAULT 00000000           COMMENT '最低位为客房管理权限，第二位是用户管理权限，第三位为查看交易记录和报表权限'
    )
    DEFAULT CHARSET = 'utf8';

    # 房间类型表：保存设置的房间类型
    CREATE TABLE roomtype(
        `typeid`          INT(8)            NOT NULL PRIMARY KEY AUTO_INCREMENT COMMENT '房间类型编号，自增量',
        `typename`        CHAR(20)          NOT NULL UNIQUE                     COMMENT '房间类型名，不允许同名',
        `bedsnum`         INT(2) UNSIGNED   NOT NULL DEFAULT 1,
        `price`           FLOAT(10,2)       NOT NULL,
        `description`     VARCHAR(100)      NOT NULL DEFAULT ''
    );

    # 房间表：保存所有客房信息
    CREATE TABLE rooms(
        `roomid`          CHAR(8)           NOT NULL PRIMARY KEY,
        `floor`           CHAR(8)           NOT NULL,
        `status`          ENUM('used',
                               'idle',
                               'invalid')   NOT NULL                            COMMENT '房间当前状态：used为已被入住，idle为空闲，invalid为不可用',
        `price`           FLOAT(10,2)       NOT NULL,
        `description`     VARCHAR(100)      NOT NULL DEFAULT '',
        `roomtypeid`      INT(8)            NOT NULL,
        FOREIGN KEY(roomtypeid) REFERENCES roomtype(typeid)
    );

    # 团队信息表：保存所有团队信息
    CREATE TABLE groups(
        `groupid`         INT(8)            NOT NULL PRIMARY KEY AUTO_INCREMENT COMMENT '团队编号，自增量',
        `groupname`       CHAR(20)          NOT NULL,
        `membersnum`      INT(3) UNSIGNED   NOT NULL                            COMMENT '成员数指需要登记的成员数，即团队需要的房间数，而非团队总人数',
        `contactname`     CHAR(20)          NOT NULL                            COMMENT '团队联系人姓名',
        `contacttel`      CHAR(20)          NOT NULL                            COMMENT '团队联系人电话'
    );

    # 顾客信息表：保存所有顾客信息
    CREATE TABLE guests(
        `guestid`         INT(20)           NOT NULL PRIMARY KEY AUTO_INCREMENT COMMENT '客户编号，自增量',
        `IDnumber`        CHAR(20),
        `name`            CHAR(20)          NOT NULL,
        `roomid`          CHAR(8)                                               COMMENT '占用房号，只用在住阶段的顾客该属性值不为空',
        `groupid`         INT(8)                                                COMMENT '散客不填',
        `telephone`       CHAR(20)          NOT NULL,
        `photo`           MEDIUMBLOB                                            COMMENT '顾客照片',
        `sex`             ENUM('M','F')                                         COMMENT '由身份证获得的顾客性别',
        `birthday`        DATE                                                  COMMENT '由身份证获得的顾客生日，用于计算年龄',
        FOREIGN KEY(groupid) REFERENCES groups(groupid)
    );

    # 交易记录表：保存各类交易记录
    CREATE TABLE records(
        `orderid`         CHAR(20)          NOT NULL PRIMARY KEY                COMMENT '订单号，首位字母P表示预约型，I为入住型，O表示退房型；后跟14位订单建立时间和5位随机数',
        `ordertype`       ENUM('CheckIn',
                               'CheckOut',
                               'Order')     NOT NULL                            COMMENT '订单类型：Order为预约订单，CheckIn为入住订单，CheckOut为退房订单',
        `guesttype`       ENUM('Solo',
                               'Group')     NOT NULL                            COMMENT '订单顾客类型',
        `guestid`         INT(20)                                               COMMENT '散客预约/入住/退房：填写顾客id，团体入住时也要求填写顾客id',
        `groupid`         INT(8)                                                COMMENT '团体预约/入住/退房：填写团体id',
        `roomid`          CHAR(8)                                               COMMENT '填写房间号',
        `time`            DATETIME          NOT NULL                            COMMENT '预约：预约入住时间，入住：入住时间，退房：退房时间',
        `bill`            FLOAT(10,2)       NOT NULL DEFAULT 0.00               COMMENT '退房时填写，账单总价',
        `payment`         ENUM('Cash',
                               'Card',
                               'Online')                                        COMMENT '付款方式：Cash为现金支付，Card为银行卡支付，Online为在线支付',    
        `predictdays`     INT(5) UNSIGNED   NOT NULL                            COMMENT '预约：预约天数，入住：预计居住天数，退房：实际居住天数',
        `memo`            VARCHAR(200)                                          COMMENT '订单备注',
        FOREIGN KEY(roomid) REFERENCES rooms(roomid)
    );

# ------------------------------------------------- #
# 创建视图
# ------------------------------------------------- #
    # 散客与团体入住记录视图
    CREATE VIEW checkin_info AS
    SELECT DISTINCT records.*,ADDDATE(`time`,predictdays) as end_time, if(ISNULL(records.guestid),groups.groupname,guests.name) AS name
    FROM records,guests,groups
    WHERE ordertype='CheckIn' AND if(ISNULL(records.guestid),groups.groupid,guests.guestid)=ifnull(records.guestid,records.groupid)
    ORDER BY `time` DESC;
    # 散客与团体退房记录视图
    CREATE VIEW checkout_info AS
    SELECT DISTINCT records.*, guests.name as `name`
    FROM records,guests
    WHERE ordertype='CheckOut' AND guests.guestid=records.guestid;
    # 散客与团体预约记录视图
    CREATE VIEW order_info AS
    SELECT DISTINCT records.*,ADDDATE(`time`,predictdays) as end_time, if(ISNULL(records.guestid),groups.groupname,guests.name) AS name
    FROM records,guests,groups
    WHERE ordertype='Order' AND if(ISNULL(records.guestid),groups.groupid,guests.guestid)=ifnull(records.guestid,records.groupid);
    # 房间信息视图
    CREATE VIEW room_info AS
    SELECT rooms.roomid, rooms.floor, rooms.status, rooms.price, rooms.description, roomtype.typename, roomtype.bedsnum
    FROM rooms,roomtype
    WHERE roomtype.typeid=rooms.roomtypeid;
    # 当前在住的客户信息视图
    CREATE VIEW guest_info AS
    SELECT guests.guestid, guests.name, guests.roomid, guests.telephone, guests.sex, guests.birthday,
        MAX(checkin_info.time) as checkin_time, 
        substring_index(GROUP_CONCAT( `predictdays` order BY `time` desc separator " "), " ", 1) as predictdays,
        date_format(substring_index(GROUP_CONCAT(checkin_info.end_time order BY `time` desc SEPARATOR ","), ",", 1),"%Y-%m-%d") AS end_time
    FROM guests,room_info,checkin_info
    WHERE room_info.roomid=guests.roomid AND checkin_info.roomid=guests.roomid AND NOT ISNULL(guests.roomid)
    GROUP BY checkin_info.roomid;

# ------------------------------------------------- #
# 创建超级用户
# ------------------------------------------------- #
    INSERT INTO admins SET username='admin', password=md5("123456myhms"), name='管理员', permissions=b'01111111';

# ------------------------------------------------- #
# 创建测试用例
# ------------------------------------------------- #
    INSERT INTO `admins` (`username`, `password`, `name`, `sex`, `job`, `telephone`, `permissions`) VALUES
        ('hanbin', '7b6a7a646d09155a7088e694457e148c', '韩彬', 'M', '前台', '18128789181', b'00000101');

    INSERT INTO `roomtype` (`typeid`, `typename`, `bedsnum`, `price`, `description`) VALUES
        (1, '标准单人房', 1, 280.00, '有wifi，有窗，无餐食，18-24平方米，一张1.8米大床'),
        (2, '标准双人房', 2, 380.00, '有wifi，有窗，无餐食，24-30平方米，两张1.5米床'),
        (3, '标准三人房', 3, 480.00, '有wifi，有窗，无餐食，30-40平方米，三张1.35米床'),
        (4, '商务单人房', 1, 250.00, '有wifi，有窗，无餐食，15-18平方米，一张1.8米大床'),
        (5, '豪华套房', 2, 580.00, '有wifi，有窗，提供餐食，40-50平方米，两张1.8大床');

    INSERT INTO `rooms` (`roomid`, `floor`, `status`, `price`, `description`, `roomtypeid`) VALUES
        ('A101', '1', 'idle', 280.00, '有wifi，有窗，无餐食，18-24平方米，一张1.8米大床', 1),
        ('A102', '1', 'idle', 300.00, '有wifi，有窗，无餐食，18-24平方米，一张1.8米大床', 1),
        ('A103', '1', 'idle', 280.00, '有wifi，有窗，无餐食，18-24平方米，一张1.8米大床', 1),
        ('A104', '1', 'idle', 260.00, '有wifi，无窗，无餐食，18-24平方米，一张1.8米大床', 1),
        ('A105', '1', 'idle', 280.00, '有wifi，有窗，无餐食，18-24平方米，一张1.8米大床', 1),
        ('A106', '1', 'idle', 280.00, '有wifi，有窗，无餐食，18-24平方米，一张1.8米大床', 1),
        ('A107', '1', 'idle', 280.00, '有wifi，有窗，无餐食，18-24平方米，一张1.8米大床', 1),
        ('A108', '1', 'idle', 280.00, '有wifi，有窗，无餐食，18-24平方米，一张1.8米大床', 1),
        ('A201', '2', 'idle', 380.00, '有wifi，有窗，无餐食，24-30平方米，两张1.5米床', 2),
        ('A203', '2', 'idle', 480.00, '有wifi，有窗，无餐食，30-40平方米，三张1.35米床', 3),
        ('A301', '3', 'idle', 250.00, '有wifi，有窗，无餐食，15-18平方米，一张1.8米大床', 4),
        ('A401', '4', 'idle', 580.00, '有wifi，有窗，提供餐食，40-50平方米，两张1.8大床', 5),
        ('A506', '5', 'idle', 580.00, '有wifi，有窗，提供餐食，40-50平方米，两张1.8大床', 5),
        ('B202', '2', 'idle', 380.00, '有wifi，有窗，无餐食，24-30平方米，两张1.5米床', 2),
        ('B204', '2', 'idle', 480.00, '有wifi，有窗，无餐食，30-40平方米，三张1.35米床', 3),
        ('B302', '3', 'idle', 250.00, '有wifi，有窗，无餐食，15-18平方米，一张1.8米大床', 4),
        ('B402', '4', 'invalid', 580.00, '有wifi，有窗，提供餐食，40-50平方米，两张1.8大床', 5);

    INSERT INTO `groups` (`groupid`, `groupname`, `membersnum`, `contactname`, `contacttel`) VALUES
        (1, '禅之旅', 3, '张三', '13311112222');