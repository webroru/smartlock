create table if not exists `lock`
(
    id int auto_increment
        primary key,
    passcode_id varchar(200) not null,
    passcode varchar(200) null,
    name varchar(200) null,
    start_date datetime not null,
    end_date datetime not null
) COLLATE='utf8_general_ci';

create table if not exists booking
(
    id int auto_increment
        primary key,
    name varchar(200) not null,
    check_in_date datetime not null,
    check_out_date datetime not null,
    order_id varchar(15) null,
    property varchar(15) null,
    lock_id int default null null,
    FOREIGN KEY (lock_id) REFERENCES `lock`(id)
) COLLATE='utf8_general_ci';

create table if not exists token
(
    id int auto_increment
        primary key,
    access_token varchar(200) not null,
    refresh_token varchar(200) null,
    expiration_time datetime not null
) COLLATE='utf8_general_ci';
