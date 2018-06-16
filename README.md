# Aperire

Crowdsourcing mobile web application to rank ideas to create policy sets using network analysis. Aperire is a latin work for "to open", which is the vision of this software - to open policy making to the public.

## Requirements

* node v8+
* Mysql

## Installing

Download the code or use git:

```bash
git clone git@github.com:dortheimer/aperire.git

cd aperire
npm install
```

create a local configuration file and edit the required fields:

```bash
cp config/default.json config/local.json
vi config config/local.json
```

Create a Mysql database:

```bash
 mysql -uusername -p
mysql> create database aperire;
```

Run the schema building script

```bash
node bin/init_db
```

Run the server using:

```bash
node bin/aperire
```

For production it is recommended running it with pm2 and apache.
For development run it with npm and nodemon:

```bash
npm start
```

## License

This is open-source software under the MIT license.
I'd be glad to ge an email if you use Aperire.