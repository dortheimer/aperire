# Aperire

Crowdsourcing mobile web application to rank ideas to create policy sets using network analysis. Aperire is a latin work for "to open", which is the vision of this software - to open policy making to the public.

## Installing


Software you need to to run Aperire:
1. Apache
1.1 Make sure mod_rewrite is enabled
2. MySQL database
3. PHP running on Apache
3.1. Zend framework 1.x 

Install Aperire:


 mysql -uusername -p
mysql> create database aperire;
 mysql -udev -p aperire < /var/www/aperire/sql/initial.sql 

1. Download the code and put it in folder accessable by Apache.
2. Add this configuration to the Vhost section:
<VirtualHost *:80>
    DocumentRoot "/path_to_aperire_folder/public"
    ServerName your_domain.com
</VirtualHost>
3. Log in to Mysql and create a new database.
4. Import file /sql/initial.sql to create the database schema

# License

This is open-source software under the MIT license.