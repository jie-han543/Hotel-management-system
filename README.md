# Hotel-management-system
based on Browser/Server Architecture

### 项目介绍
采用关系型数据库管理系统 MySQL，基于 B/S 架构完成了一个客房管理系统。网页前端框架采用了 Vue.js ，UI界面设计使用了 Element 组件库；后端语言则采用了 PHP 。  

**项目需求**  
+ 功能需求：
    1.	具有方便的登记、结账功能，以及预订客房的功能，能够支持团体登记和团体结账。 
    2.	能快速、准确地了解宾馆内的客房状态，以便管理者决策。 
    3.	提供多种手段查询客人的信息。  
    4.	具备一定的维护手段，具备有一定权利的操作员在密码的支持下才有可更改房价、房间类型、增减客房。  
    5.	完善的结账报表系统。
+ 性能需求：要求实现B/S架构，允许多用户同时操作并不会出现错误。
+ 环境需求：服务器要求支持PHP7的Web服务器，数据库服务器要求MySQL10以上。系统支持所有兼容es5的浏览器，但不支持IE8及以下版本。
+ 安全性需求：系统应通过密码进行身份认证。
+ 保密性需求：系统应利用md5散列值防止密码泄露。
+ 可维护性需求：系统客房信息可以修改。包括更改房价、房间类型、增减客房等。

**结构设计**  

![image](https://user-images.githubusercontent.com/57163528/156515244-4e77b612-5bed-451d-8e3a-b739bfc3ea36.png)

![image](https://user-images.githubusercontent.com/57163528/156515223-e5010f0f-2c4c-476d-bbc9-add763b0b663.png)

**文件结构**
```
hms  系统总文件夹
  client  客户端文件夹
      common  常规文件夹：存放引入的文件
        axios.min.js  axios.js脚本文件
        common.js  通用js函数文件
        index.js  Element脚本文件
        vue.js  Vue.js脚本文件 
      components  Vue组件文件夹
        portrait.html	 头像组件
        roomcard.html	 房间展示卡片组件
      themes  主题和字体文件夹：存放涉及主题及字体
      view  页面文件夹：界面Main部分的内容
        checkin-page.html	 入住功能页面
        checkout-page.html  退房功能页面
        guest-page.html  客户信息页面
        home-page.html  主页，其他页面
        login-page.html  登录页
        order-page.html  预约功能页面
        records-page.html  交易记录与报表页面
        rooms-page.html  房间详细页面
        users-page.html  用户信息页面
        view-page.html  全部房间当前状态展示页面
      index.php  总html文件，该目录下所有其它文件内容和Vue组件文件夹下的文件内容均被插入到此文件中，组合成一段完整的html代码。
  pics  图片文件夹
      admin.png	 默认管理员头像
      default.png  系统标志
      default2.png
      guests  客户相片文件夹：保存入住客户照片
        default.jpeg  默认客户图片
  server  服务器端文件夹
      admins.php  修改当前用户个人信息
      adminspw.php  修改当前用户密码
      changeroom.php  增加/修改/删除房间信息
      changeroomtype.php  增加/修改/删除房间类型信息
      changeuser.php  增加/修改/删除用户信息
      checkIn.php  提交散客入住订单
      checkIn2.php 提交团队入住订单
      checkinguest.php  退房时获取入住的散客订单
      checkin_groupmembers.php  获取团体成员信息
      checkout.php  提交散客退房订单
      checkout2.php  提交团队退房订单
      guestpics.php  获取某个顾客的照片
      guestsinfo.php  获取显示的顾客信息和团队信息
      login.php  登录时获取用户名及密码散列值用于比对
      order.php  提交预约订单
      page1info.php  获取首页显示内容
      page2info.php  获取预约时显示的房间信息
      records1.php  显示预约记录报表
      records2.php  显示入住记录报表
      records3.php  显示退房记录报表
      records4.php  显示每日收入报表
      records5.php  显示每月收入报表
      roomcardinfo.php  获取某一间房的详细信息和当前入住顾客的信息
      roomsinfo.php  获取客房管理初始显示的客房和客房类型信息
      searchcheckin.php  搜索团体入住订单
      searchorder.php  入住时搜索散客预约订单
      searchorder2.php  入住时搜索团体预约订单
      showidlerooms.php  根据预抵时间和时长获取可被预约的房间信息
      uploadphoto.php  保存客户的照片
      uploadpic.php  上传用户头像
      usersinfo.php  获取用户管理初始显示的用户信息
      config.ini  数据库连接配置文件
      setup.sql  数据库创建文件
      test.sql  数据库导入测试数据文件
```
调用关系  
  
![image](https://user-images.githubusercontent.com/57163528/156514484-02d9798d-484b-4ec6-8f15-42eb81c64a14.png)

**部分界面**
  
![image](https://user-images.githubusercontent.com/57163528/156514697-a9a387d8-2c09-45d2-8542-1667bd015912.png)  
![image](https://user-images.githubusercontent.com/57163528/156514731-46292717-c913-46ab-9ff0-479580077e6a.png)  
![image](https://user-images.githubusercontent.com/57163528/156514747-c5abf018-a800-4570-9f1c-905baea8b924.png)  
![image](https://user-images.githubusercontent.com/57163528/156514778-4debee2a-2857-4c4f-84c5-4e854befe49d.png)  
![image](https://user-images.githubusercontent.com/57163528/156514822-8dd32853-813a-4f84-a37c-4f836079184a.png)  

