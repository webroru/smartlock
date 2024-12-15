create table booking_has_room
(
    booking_id int not null,
    room_id int not null
);

alter table booking_has_room
    add constraint booking_has_room_booking_id_fk
        foreign key (booking_id) references booking (id)
            on delete cascade;

alter table booking_has_room
    add constraint booking_has_room_room_fk
        foreign key (room_id) references room (id)
            on delete cascade;
