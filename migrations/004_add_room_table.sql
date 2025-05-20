create table room
(
    id int auto_increment primary key,
    lock_id varchar(15) not null,
    number varchar(15) not null
);

create unique index room_number_uindex
    on room (number);
