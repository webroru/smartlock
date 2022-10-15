alter table `lock`
    add room_id int not null;

update `lock` l
set l.room_id = (select id from room where number = 'main')
where true;

alter table `lock`
    add constraint lock_room_id_fk
        foreign key (room_id) references room (id)
            on delete cascade;
