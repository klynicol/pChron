# PChron

### Soon to be the number one frozen pizza website in the world!

## Installation

1. Install a local PHP and MySql capable application.
   - I use Xampp from https://www.apachefriends.org/index.html
   - You will also need to add PHP as an executable on your computer... Something like https://john-dugan.com/add-php-windows-path-variable/ if you're on windows.
2. Start your local PHP server and MySql server.
3. Create a local MySql database called `pcron`
4. Clone this project into your local repository.
   - The repository has to be accessible by your local PHP server. For me that means putting them in C:/xampp/htdocts to be accessed by Xampp.
5. Here's the tricky one. You will need to visit http://MarkWickline.com/pChron and download 3 php config files with the password I provided.
   - These are files I do not want to host on github
   - Stick them all in the project folder in pcserver/application/config
6. Open a terminal and navigate to the project.
7. run `node -v` to make sure you have node v 10.3+
8. run `npm install` to get local packages
9. run `php pcserver/index.php Install` to intilize the database with tables and data.
10. run `npm run dev` to start the application
11. Navigate to http://localhost:3000
12. Profit!

### Backend Dependencies

- https://github.com/chriskacerguis/ci-restserver
- https://github.com/guzzle/guzzle
- https://github.com/lcobucci/jwt
