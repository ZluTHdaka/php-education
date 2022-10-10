drop table if exists articles;
create table if not exists articles
(
    id serial,
    name  varchar(255),
    article text,
    created_at timestamp default localtimestamp(0)
);
set time zone 'Europe/Moscow';
insert into articles (name, article)
values ('bibizyana', 'bibizyana poshla na ulitsu i nashla banan'),
       ('slon', 'slon prosto poshel nahuy');
