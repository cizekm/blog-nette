Simple blog application
=================

This is a simple blog application based on Nette framework.

Requirements
------------

PHP 7.1 or higher.
MySQL 5.5 or higher


Installation and setup
------------

1. in your htdocs (or wwwroot) directory run ```$ git clone https://github.com/cizekm/blog-nette.git ./blog```. Your application will be located in wwwroot/blog. 
2. make directories `temp/` and `log/` writable for web server
3. create empty database and user who has access to it
4. create local config file `app/config/config.local.neon` from `app/config/config.local.neon.dist` and set all required properties:
    - db settings (username, password and database name)
    - base URL - the root URL of your application. It must point to application's `www` directory (eg. https://localhost/blog/www, or https://my-blog.local if you have set virtual host my-blog.local within your web server)
    - adminUsers - associative array where key is username and value is password. These users will get access to private part
5. run ```$ composer install``` in your application root directory to install all dependencies
6. run ```$ php www/index.php orm:schema-tool:create``` in your application root directory. This should create complete database structure in your empty database.
7. enjoy :-)

Public part
------------

Public part is located in the www root directory, eg. `https://localhost/blog/www` etc.

Private part
------------

Private part is located in `BASE_URL/admin`, eg. `https://localhost/blog/www/admin` etc.

REST API
------------

REST API has two endpoints:

1. `BASE_URL/api/articles` (eg. `https://localhost/blog/www/api/articles`) which provides list of all visible and published articles
2. `BASE_URL/api/article/<id>` (eg. `https://localhost/blog/www/api/article/1`) (where id is id of the article provided by articles list) which provides single article detail information
 

**It is CRITICAL that whole `app/`, `log/` and `temp/` directories are not accessible directly
via a web browser. See [security warning](https://nette.org/security-warning).**
