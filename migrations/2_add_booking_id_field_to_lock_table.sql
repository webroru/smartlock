update `lock` l
inner join booking b on l.id = b.lock_id
set l.booking_id = b.id
where l.id = b.lock_id;

delete from `lock` l where l.booking_id = 0;

alter table `lock`
add constraint lock_booking_id_fk
foreign key (booking_id) references booking (id)
on delete cascade;

