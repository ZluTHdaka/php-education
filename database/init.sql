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
drop table if exists comments;
create table if not exists comments
(
    id serial primary key,
    article_id  varchar(255),
    comment text,
    created_at timestamp default localtimestamp(0)
);
set time zone 'Europe/Moscow';
insert into comments (article_id, comment)
values (1, 'Bibizyana horosha! :D'),
       (2, 'Slon realno poshel nahuy >:(');

