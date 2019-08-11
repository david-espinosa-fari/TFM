create database if not exists meteosalle;
use meteosalle;
create table if not exists user(
                     id int not null auto_increment,
                     uuid_user varchar(30) not null default "bvaa",
                     address varchar (35) default null,
                     nombre varchar (25) default "David",
                     apellido varchar (35) default "Espinosa",
                     primary key id(id)

)engine = innoDb default charset = utf8;

create table if not exists station(
                     id int not null auto_increment,
                     uuid_user varchar(30) not null default "bvaa",
                     uuid_station varchar (35) default null,
                     latitud char (25) default "David",
                     longitud char (35) default "Espinosa",
                     population char (35) default "08720",
                     temp int(4) default 28,
                     primary key id(id)

)engine = innoDb default charset = utf8;