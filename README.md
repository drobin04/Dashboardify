# Legacy PHP Site Setup

If you're not familiar with docker, download and install Docker Desktop, and then open this directory in a terminal and run 'docker build .' , and then wait for it to complete the build process to build an image in docker.

Then, open Docker Desktop's 'Images' page, find your new image created for this app, and click start.

Under optional settings, configure ports / port mappings for 80 / 443. You can feel free to map them to 8080 or anything else.

You need to map a volume for the container path /var/www/html/Dashboardify/data , and copy the included Dashboardify.s3db demo database to this folder afterwards. 

Next you can go to the Containers section of Docker Desktop and start the newly created container instance.

Now you can sign into Dashboardify (your url should be server:port/Dashboardify/ ) using the default user 'DashboardifyAdmin', pass 'DashboardifyAdmin!'. 

You should immediately change the default password. 
