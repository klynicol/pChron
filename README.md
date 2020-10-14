# PChron

### Soon to be the number one frozen pizza website in the world!

## Installation

1. Install a local PHP and mysql capable application.
   - I use Xampp from https://www.apachefriends.org/index.html
   - You will also need to add PHP as an executable on your computer... Something like https://john-dugan.com/add-php-windows-path-variable/.
2. Start your local PHP server and mysql server.
3. Clone this project into your local repository.
   - The repository has to be accessible by your local PHP server.
4. Open a terminal and navigate to the folder.
5. Here's the tricky one. You will need to visit MarkWickline.com/pChron and download 3 php config files with the password I provided.
   - These are files I do not want to host on github
   - Stick them all in pcserver/application/config
6. run `node -v` to make sure you have node v 10.3+
7. run `npm install` to get local packages
8. Create a local MySql database called `pcron`
9. run `php pcserver/index.php Install` to intilize the database with tables and data.
10. run `npm run dev` to start the application
11. Navigate to localhost:3000
12. Profit!

### Backend Dependencies

- https://github.com/chriskacerguis/ci-restserver
- https://github.com/guzzle/guzzle
- https://github.com/lcobucci/jwt
