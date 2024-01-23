# SFL COS Services Layer


#### Setup Environment Variable
Setting up environment variable for accessing configuration file from server using CLI & Web Server.

> **CLI:** Edit **~/.bashrc** file and add below lines end of file & run this command  ***source ~/.bashrc***.

    export SFL_COS_APP_NAME="SFL-COS-SERVICES";
    export SFL_COS_APP_ENV="local";
    export SFL_COS_APP_KEY="";
    export SFL_COS_APP_DEBUG=false;
    export SFL_COS_APP_URL="http://sfl-cos-services.dv";
    export SFL_COS_DB_MYSQL_CONNECTION="mysql";
    export SFL_COS_DB_MYSQL_HOST="localhost";
    export SFL_COS_DB_MYSQL_PORT="3306";
    export SFL_COS_DB_MYSQL_DATABASE="sfl";
    export SFL_COS_DB_MYSQL_USERNAME="root";
    export SFL_COS_DB_MYSQL_PASSWORD="root@123";
    export SFL_COS_DB_MONGO_CONNECTION="mongodb";
    export SFL_COS_DB_MONGO_HOST="localhost";
    export SFL_COS_DB_MONGO_PORT="27017";
    export SFL_COS_DB_MONGO_DATABASE="sfl";
    export SFL_COS_DB_MONGO_USERNAME="";
    export SFL_COS_DB_MONGO_PASSWORD="";
    export SFL_COS_REDIS_CLIENT="predis";
    export SFL_COS_REDIS_HOST="localhost";
    export SFL_COS_REDIS_PASSWORD=null;
    export SFL_COS_REDIS_PORT="6379";
    export SFL_COS_REDIS_DB="4";
    export SFL_COS_REDIS_CACHE_DB="4";
    export SFL_COS_JWT_TOKEN="JwqoTh241CRnmbdXaHVy7HvcoUzEJvTdFJq6Cw8fbJhteYyEZfJ40tAc87ztn6S7"
	
> **Web Server:** Edit **/etc/nginx/fastcgi_params** file and add below lines end of file  and the restart both ***php-fpm & nginx*** services.

    fastcgi_param SFL_COS_APP_NAME "SFL-COS-SERVICES";
    fastcgi_param SFL_COS_APP_ENV "local";
    fastcgi_param SFL_COS_APP_KEY "";
    fastcgi_param SFL_COS_APP_DEBUG true;
    fastcgi_param SFL_COS_APP_URL "http://sfl-cos-services.dv";
    fastcgi_param SFL_COS_DB_MYSQL_CONNECTION "mysql";
    fastcgi_param SFL_COS_DB_MYSQL_HOST "localhost";
    fastcgi_param SFL_COS_DB_MYSQL_PORT "3306";
    fastcgi_param SFL_COS_DB_MYSQL_DATABASE "sfl";
    fastcgi_param SFL_COS_DB_MYSQL_USERNAME "root";
    fastcgi_param SFL_COS_DB_MYSQL_PASSWORD "root@123";
    fastcgi_param SFL_COS_DB_MONGO_CONNECTION "mongodb";
    fastcgi_param SFL_COS_DB_MONGO_HOST "localhost";
    fastcgi_param SFL_COS_DB_MONGO_PORT "27017";
    fastcgi_param SFL_COS_DB_MONGO_DATABASE "sfl";
    fastcgi_param SFL_COS_DB_MONGO_USERNAME "";
    fastcgi_param SFL_COS_DB_MONGO_PASSWORD "";
    fastcgi_param SFL_COS_REDIS_CLIENT "predis";
    fastcgi_param SFL_COS_REDIS_HOST "localhost";
    fastcgi_param SFL_COS_REDIS_PASSWORD null;
    fastcgi_param SFL_COS_REDIS_PORT "6379";
    fastcgi_param SFL_COS_REDIS_DB "4";
    fastcgi_param SFL_COS_REDIS_CACHE_DB "4";
    fastcgi_param SFL_COS_JWT_TOKEN "JwqoTh241CRnmbdXaHVy7HvcoUzEJvTdFJq6Cw8fbJhteYyEZfJ40tAc87ztn6S7";