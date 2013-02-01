DROP TABLE IF EXISTS admin;
create table `admin` (
`id` int(11) not null  primary key auto_increment ,
`admin_name` varchar(20) not null    ,
`admin_pwd` varchar(32) not null    ,
`group_id` int(11) not null    ,
`last_login` int(11) null    ,
`last_login_ip` varchar(20) null    
)engine=MyISAM charset=utf8;

DROP TABLE IF EXISTS admin_group;
create table `admin_group` (
`id` int(11) not null  primary key auto_increment ,
`group_name` varchar(20) not null    ,
`authority` char(44) not null    
)engine=MyISAM charset=utf8;

DROP TABLE IF EXISTS admin_msg;
create table `admin_msg` (
`id` int(11) not null  primary key auto_increment ,
`msg_from` int(11) not null    ,
`msg_from_name` varchar(20) not null    ,
`msg_to` int(11) not null    ,
`msg_title` char(11) not null    ,
`msg_content` text null    ,
`is_read` tinyint(1) not null default '0'   ,
`msg_time` int(11) unsigned not null    ,
`admin_msg` tinyint(1) not null default '1'   
)engine=MyISAM charset=utf8;

DROP TABLE IF EXISTS ads;
create table `ads` (
`aid` int(8) unsigned not null  primary key auto_increment ,
`name` varchar(100) not null    ,
`url` varchar(250) not null    ,
`target` varchar(20) not null default '_blank'   ,
`note` varchar(250) not null    ,
`pic` varchar(250) not null    ,
`width` int(4) not null    ,
`height` int(4) not null    ,
`show_num` int(12) not null default '0'   ,
`hit_num` int(12) not null default '0'   ,
`is_show` tinyint(1) not null    
)engine=MyISAM charset=utf8;

DROP TABLE IF EXISTS book;
create table `book` (
`book_id` int(11) unsigned not null  primary key auto_increment ,
`book_name` varchar(80) not null    ,
`author_id` int(11) unsigned not null default '0'   ,
`author` varchar(30) not null    ,
`poster_id` int(11) unsigned not null default '0'   ,
`poster` varchar(30) not null    ,
`post_time` int(11) unsigned not null    ,
`keywords` varchar(50) null    ,
`sort_id` smallint(3) unsigned not null default '0'   ,
`works_sid` int(11) unsigned not null default '0'   ,
`is_vip` tinyint(1) unsigned not null default '0'   ,
`is_power` tinyint(1) unsigned not null default '0'   ,
`is_first` tinyint(1) unsigned not null default '0'   ,
`is_full` tinyint(1) unsigned not null default '0'   ,
`image_url` varchar(100) null    ,
`introduce` text not null    ,
`last_update` int(11) unsigned not null    ,
`last_chapter` varchar(100) not null default '0'   ,
`last_chapterid` int(11) unsigned not null default '0'   ,
`total_size` int(11) unsigned not null default '0'   ,
`day_visit` int(11) unsigned not null default '0'   ,
`week_visit` int(11) unsigned not null default '0'   ,
`month_visit` int(11) unsigned not null default '0'   ,
`all_visit` int(11) unsigned not null default '0'   ,
`day_vote` int(11) unsigned not null default '0'   ,
`week_vote` int(11) unsigned not null default '0'   ,
`month_vote` int(11) unsigned not null default '0'   ,
`all_vote` int(11) unsigned not null default '0'   ,
`store_num` int(11) unsigned not null default '0'   ,
`if_check` tinyint(1) unsigned not null default '0'   ,
`if_display` tinyint(1) unsigned not null default '0'   ,
`if_recommend` tinyint(1) unsigned not null default '0'   
)engine=MyISAM charset=utf8;

DROP TABLE IF EXISTS book_chapter;
create table `book_chapter` (
`chapter_id` int(11) unsigned not null  primary key auto_increment ,
`chapter_name` varchar(80) not null    ,
`chapter_type` tinyint(1) unsigned not null default '0'   ,
`chapter_order` int(11) unsigned not null default '0'   ,
`book_id` int(11) unsigned not null    ,
`volume_id` int(11) unsigned not null default '0'   ,
`poster_id` int(11) unsigned not null    ,
`poster` varchar(60) not null    ,
`post_time` int(11) unsigned not null    ,
`chapter_detail` mediumtext null    ,
`chapter_size` int(11) unsigned not null default '0'   ,
`is_draft` tinyint(4) unsigned not null default '0'   ,
`update_time` int(11) unsigned not null    ,
`is_vip` tinyint(1) unsigned not null default '0'   ,
`sale_price` int(8) unsigned not null default '0'   ,
`sale_num` int(11) unsigned not null default '0'   ,
`sale_total` int(11) unsigned not null default '0'   ,
`if_check` tinyint(1) unsigned not null default '0'   
)engine=MyISAM charset=utf8;

DROP TABLE IF EXISTS book_favorite;
create table `book_favorite` (
`id` int(11) unsigned not null  primary key auto_increment ,
`user_id` int(11) unsigned not null    ,
`book_id` int(11) unsigned not null    ,
`chapter_id` int(11) unsigned not null default '0'   ,
`add_time` int(11) unsigned not null    ,
`last_visit` int(11) unsigned not null    
)engine=MyISAM charset=utf8;

DROP TABLE IF EXISTS book_recommend;
create table `book_recommend` (
`id` int(11) unsigned not null  primary key auto_increment ,
`sort_id` int(6) unsigned not null    ,
`book_id` int(11) unsigned not null    ,
`order` smallint(4) unsigned not null    ,
`add_time` int(11) unsigned not null    
)engine=MyISAM charset=utf8;

DROP TABLE IF EXISTS book_recommend_sort;
create table `book_recommend_sort` (
`sort_id` int(11) unsigned not null  primary key auto_increment ,
`sort_name` varchar(30) not null    
)engine=MyISAM charset=utf8;

DROP TABLE IF EXISTS book_reply;
create table `book_reply` (
`reply_id` int(11) unsigned not null  primary key auto_increment ,
`review_id` int(11) unsigned not null    ,
`is_topic` tinyint(1) unsigned not null default '0'   ,
`poster_id` int(11) unsigned not null    ,
`poster` varchar(30) not null    ,
`post_time` int(11) unsigned not null    ,
`post_ip` varchar(25) not null    ,
`subject` varchar(100) not null    ,
`detail` text not null    ,
`if_display` tinyint(1) unsigned not null default '0'   
)engine=MyISAM charset=utf8;

DROP TABLE IF EXISTS book_review;
create table `book_review` (
`review_id` int(11) unsigned not null  primary key auto_increment ,
`book_id` int(11) unsigned not null    ,
`replier_id` int(11) unsigned not null default '0'   ,
`replier` varchar(30) null    ,
`reply_time` int(11) unsigned not null    ,
`view_num` int(11) unsigned not null default '0'   ,
`reply_num` int(11) unsigned not null default '0'   ,
`if_lock` tinyint(1) unsigned not null default '0'   ,
`if_top` tinyint(1) unsigned not null default '0'   ,
`if_good` tinyint(1) unsigned not null default '0'   ,
`if_check` tinyint(1) not null default '0'   
)engine=MyISAM charset=utf8;

DROP TABLE IF EXISTS book_search;
create table `book_search` (
`sid` int(12) unsigned not null  primary key auto_increment ,
`tid` tinyint(1) unsigned not null default '1'   ,
`keyword` varchar(30) not null    ,
`snum` int(12) not null default '1'   ,
`result` tinyint(1) unsigned not null default '1'   
)engine=MyISAM charset=utf8;

DROP TABLE IF EXISTS book_sort;
create table `book_sort` (
`sort_id` smallint(4) unsigned not null  primary key auto_increment ,
`sort_name` varchar(20) not null    ,
`super_id` smallint(4) unsigned not null default '0'   ,
`sort_order` int(4) unsigned not null default '1'   ,
`sort_dir` varchar(20) null    ,
`path` varchar(250) not null    
)engine=MyISAM charset=utf8;

DROP TABLE IF EXISTS book_vote;
create table `book_vote` (
`vote_id` int(8) unsigned not null  primary key auto_increment ,
`book_id` int(11) unsigned not null    ,
`poster_id` int(11) unsigned not null    ,
`poster` varchar(30) not null    ,
`post_time` int(11) unsigned not null    ,
`start_time` int(11) unsigned not null    ,
`end_time` int(11) unsigned not null    ,
`subject` varchar(80) not null    ,
`description` text not null    ,
`vote_num` int(11) not null default '0'   ,
`if_display` tinyint(1) unsigned not null default '1'   ,
`if_check` tinyint(1) unsigned not null default '1'   ,
`need_login` tinyint(1) unsigned not null default '1'   ,
`multi_select` tinyint(1) unsigned not null default '0'   ,
`use_items` tinyint(2) unsigned not null    ,
`item1` varchar(100) not null    ,
`item2` varchar(100) not null    ,
`item3` varchar(100) not null    ,
`item4` varchar(100) not null    ,
`item5` varchar(100) not null    ,
`item6` varchar(100) not null    ,
`item7` varchar(100) not null    ,
`item8` varchar(100) not null    ,
`item9` varchar(100) not null    ,
`item10` varchar(100) not null    
)engine=MyISAM charset=utf8;

DROP TABLE IF EXISTS book_vote_state;
create table `book_vote_state` (
`vote_id` int(11) unsigned not null  primary key  ,
`state1` int(11) unsigned not null default '0'   ,
`state2` int(11) unsigned not null default '0'   ,
`state3` int(11) unsigned not null default '0'   ,
`state4` int(11) unsigned not null default '0'   ,
`state5` int(11) unsigned not null default '0'   ,
`state6` int(11) unsigned not null default '0'   ,
`state7` int(11) unsigned not null default '0'   ,
`state8` int(11) unsigned not null default '0'   ,
`state9` int(11) unsigned not null default '0'   ,
`state10` int(11) unsigned not null default '0'   
)engine=MyISAM charset=utf8;

DROP TABLE IF EXISTS book_works;
create table `book_works` (
`work_id` int(11) unsigned not null  primary key auto_increment ,
`user_id` int(11) unsigned not null    ,
`user_name` varchar(30) not null    ,
`work_name` varchar(30) not null    ,
`work_template` smallint(4) unsigned not null default '0'   ,
`work_pic` varchar(100) not null    ,
`work_description` text not null    ,
`show_num` int(11) not null default '0'   ,
`if_check` tinyint(1) not null default '0'   ,
`if_recommend` tinyint(1) not null default '0'   
)engine=MyISAM charset=utf8;

DROP TABLE IF EXISTS book_works_sort;
create table `book_works_sort` (
`sort_id` int(11) unsigned not null  primary key auto_increment ,
`user_id` int(11) not null    ,
`work_id` int(11) unsigned not null    ,
`sort_name` varchar(30) not null    ,
`sort_order` smallint(4) unsigned not null    ,
`sort_description` varchar(100) not null    ,
`sort_num` int(6) unsigned not null    
)engine=MyISAM charset=utf8;

DROP TABLE IF EXISTS collector;
create table `collector` (
`collector_id` int(3) not null  primary key auto_increment ,
`collector_name` varchar(50) not null    ,
`collector_create_date` datetime not null    ,
`collector_update_date` datetime null    ,
`collector_addr` text null    ,
`collector_site_role` text null    ,
`collector_book_role` text null    ,
`collector_chapter_role` text null    
)engine=MyISAM charset=utf8;

DROP TABLE IF EXISTS config;
create table `config` (
`id` int(6) unsigned not null  primary key auto_increment ,
`type` varchar(20) not null    ,
`name` varchar(30) not null    ,
`value` text not null    ,
`description` text not null    
)engine=MyISAM charset=utf8;

DROP TABLE IF EXISTS links;
create table `links` (
`id` int(11) not null  primary key auto_increment ,
`sitename` varchar(50) not null    ,
`siteurl` varchar(100) not null    ,
`siteinstro` varchar(400) not null    ,
`status` smallint(4) not null    ,
`adminid` int(11) not null    ,
`posttime` int(11) not null    ,
`orderid` int(11) not null    
)engine=MyISAM charset=utf8;

DROP TABLE IF EXISTS style;
create table `style` (
`sid` int(8) unsigned not null  primary key auto_increment ,
`name` varchar(30) not null    ,
`value` varchar(30) not null    ,
`default` tinyint(1) not null default '0'   
)engine=MyISAM charset=utf8;

DROP TABLE IF EXISTS style_tpl;
create table `style_tpl` (
`tid` int(8) unsigned not null  primary key auto_increment ,
`sid` int(8) not null    ,
`group` varchar(30) not null    ,
`file` varchar(30) not null    ,
`note` varchar(200) not null    ,
`contents` mediumtext not null    ,
`is_system` tinyint(1) not null default '0'   
)engine=MyISAM charset=utf8;

DROP TABLE IF EXISTS temp_book;
create table `temp_book` (
`book_id` int(8) not null  primary key auto_increment ,
`book_name` varchar(50) not null    ,
`book_key` varchar(50) null    ,
`book_introduce` varchar(100) null    ,
`book_abstract` varchar(100) null    ,
`book_man` varchar(10) null    ,
`book_date` datetime null    ,
`book_sum` int(8) null    ,
`book_url` varchar(100) null    ,
`chapter_list_url` varchar(100) null    ,
`book_state` int(1) not null default '0'   ,
`book_get_date` datetime null    ,
`book_put_date` datetime null    ,
`book_affix` varchar(100) null    ,
`book_no` varchar(20) null    ,
`post_id` int(8) null    
)engine=MyISAM charset=utf8;

DROP TABLE IF EXISTS temp_book_list;
create table `temp_book_list` (
`list_id` int(8) not null  primary key auto_increment ,
`book_name` varchar(50) not null    ,
`book_url` varchar(100) not null    ,
`collector_id` int(5) not null    ,
`get_date` datetime not null    ,
`iscollect` int(1) not null default '0'   
)engine=MyISAM charset=utf8;

DROP TABLE IF EXISTS temp_chapter;
create table `temp_chapter` (
`chapter_id` int(8) not null  primary key auto_increment ,
`chapter_name` varchar(50) not null    ,
`chapter_sum` int(5) null    ,
`chapter_update` datetime null    ,
`chapter_volume` int(2) null    ,
`chapter_content_url` varchar(100) not null    ,
`chapter_state` int(1) not null default '0'   ,
`chapter_get_date` datetime null    ,
`chapter_put_date` datetime null    ,
`chapter_no` varchar(20) null    
)engine=MyISAM charset=utf8;

DROP TABLE IF EXISTS temp_content;
create table `temp_content` (
`content_id` int(8) not null  primary key auto_increment ,
`content_text` text null    ,
`content_affix` varchar(100) null    ,
`content_url` varchar(100) null    
)engine=MyISAM charset=utf8;

DROP TABLE IF EXISTS temp_link_book;
create table `temp_link_book` (
`id` int(8) not null  primary key auto_increment ,
`book_id` int(8) not null    ,
`chapter_id` int(8) not null    
)engine=MyISAM charset=utf8;

DROP TABLE IF EXISTS temp_link_chapter;
create table `temp_link_chapter` (
`id` int(8) not null  primary key auto_increment ,
`chapter_id` int(8) not null    ,
`content_id` int(8) not null    
)engine=MyISAM charset=utf8;

DROP TABLE IF EXISTS temp_link_type;
create table `temp_link_type` (
`id` int(8) not null  primary key auto_increment ,
`book_id` int(8) not null    ,
`property_id` int(2) not null    ,
`type_id` int(5) not null    
)engine=MyISAM charset=utf8;

DROP TABLE IF EXISTS temp_type;
create table `temp_type` (
`type_id` int(5) not null  primary key auto_increment ,
`type_name` varchar(10) not null    ,
`type_text` varchar(100) null    ,
`sort_id` int(4) not null default '0'   
)engine=MyISAM charset=utf8;

DROP TABLE IF EXISTS user;
create table `user` (
`id` int(11) not null  primary key auto_increment ,
`group_id` int(11) not null default '1'   ,
`user_name` varchar(20) not null    ,
`user_pwd` varchar(32) not null    ,
`email` varchar(64) not null    ,
`category` tinyint(1) not null default '0'   ,
`nickname` varchar(20) null    ,
`last_login` int(11) null    ,
`last_login_ip` varchar(20) null    ,
`is_activation` tinyint(1) not null default '0'   ,
`active_code` varchar(8) not null    
)engine=MyISAM charset=utf8;

DROP TABLE IF EXISTS user_group;
create table `user_group` (
`id` int(11) not null  primary key auto_increment ,
`group_name` varchar(20) not null    ,
`authority` char(22) not null    
)engine=MyISAM charset=utf8;

DROP TABLE IF EXISTS user_info;
create table `user_info` (
`id` int(11) not null  primary key auto_increment ,
`pwd_quiz` varchar(20) null    ,
`pwd_answer` varchar(20) null    ,
`pwd_protect_quiz` varchar(20) null    ,
`pwd_protect_answer` varchar(20) null    ,
`real_name` varchar(20) null    ,
`address` varchar(20) null    ,
`msn` varchar(20) null    ,
`birthday` varchar(20) null    ,
`accumulate` int(11) null    ,
`vip_money` int(11) null    ,
`post_nums` int(11) null    ,
`collecte_nums` int(11) null    ,
`register_time` varchar(20) null    ,
`sex` char(1) null    ,
`qq` varchar(20) null    ,
`come_from` varchar(20) null    
)engine=MyISAM charset=utf8;

DROP TABLE IF EXISTS user_msg;
create table `user_msg` (
`id` int(11) not null  primary key auto_increment ,
`msg_from` int(11) not null    ,
`msg_from_name` varchar(20) not null    ,
`msg_to` int(11) not null    ,
`msg_to_name` varchar(20) not null    ,
`msg_title` char(11) not null    ,
`msg_content` text null    ,
`is_read` tinyint(1) not null default '0'   ,
`msg_time` int(11) unsigned not null    ,
`admin_msg` tinyint(1) not null default '0'   
)engine=MyISAM charset=utf8;

