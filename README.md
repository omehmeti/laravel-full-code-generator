# laravel-full-code-generator

This laravel code generator tool is create to enable people who work with laravel create CRUD and other operations easily just by defining names and types of fields. 

Laravel code generator will enable you to easily:

1. Generate Model PHP Automatically
2. Generate Migration Script PHP Automatically
3. Generate Create Request PHP Automatically
4. Generate Update Request PHP Automatically
5. Generate Delete Request PHP Automatically
6. Generate Controller PHP Automatically

# Installing

1. First step is you can find the file called LaravelGeneratorController.php here on the project and put it in the folder where you hold your controllers (for example App\Http\Controllers).

2. Then in your routes files (which for laravel 5.4 is routes\web.php) go and add a route to call index function of this controller. So simply add the below lines to your web.php file:

Route::resource('laravel_generator','LaravelGeneratorController');

3. Just open your browser or postman and call the link: http://your_projct_name/laravel_generator

4. By default this will use the local storage and create files in below folders

\Storage\App\Controller
\Storage\App\Migration Scripts
\Storage\App\Model
\Storage\App\Request

This helped me a lot hope it will help you too.

Take care :)

Ozy
