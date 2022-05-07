create table if not exists booking
(
    id int auto_increment
        primary key,
    name varchar(200) not null,
    check_in_date datetime not null,
    check_out_date datetime not null,
    email varchar(200) null,
    code varchar(10) null,
    order_id varchar(15) null
) COLLATE='utf8_general_ci';

create table if not exists token
(
    id int auto_increment
        primary key,
    access_token varchar(200) not null,
    refresh_token varchar(200) null,
    expiration_time datetime not null
) COLLATE='utf8_general_ci';
