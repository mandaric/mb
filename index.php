<?php

# Composer autoloader
require_once 'vendor/autoload.php';

# ------------------------------------------------------------------------------

/**
 * 1. HTTP Method, GET or POST
 * 2. URI, '/info'
 * 3. Callback function that will be executed
 */
map('GET', '/info', function ()
{
    echo 'MessageBoard v1.0.0';
});

/**
 * Shows the overview of all the messages
 */
map('GET', '/', function (SQLite3 $db)
{
    $query = "SELECT * FROM messages";

    $result = $db->query($query);

    $posts = [];

    while ($post = $result->fetchArray())
    {
        array_push($posts, $post);
    }

    echo phtml('index', ['posts' => $posts], 'layout');
});

/**
 * Get a message by ID and display the form
 *
 * Post id: 2
 * Example: /edit/2
 */
map('GET', '/edit/<id>', function ($params, SQLite3 $db)
{
    $statement = $db->prepare("SELECT * FROM messages WHERE id = :id LIMIT 1");

    $statement->bindValue(':id', $params['id']);

    $result = $statement->execute();

    $post = $result->fetchArray();

    echo phtml('edit', [
        'action' => sprintf('/edit/%d', $params['id']),
        'post'   => $post
    ], 'layout');
});

/**
 * Show an empty form to create a new post
 */
map('GET', '/new', function ()
{
    echo phtml('new', ['action' => '/'], 'layout');
});

/**
 * Show a post by it's ID
 *
 * Post id: 2
 * Example: /2
 */
map('GET', '/<id>', function ($params, SQLite3 $db)
{
    $statement = $db->prepare("SELECT * FROM messages WHERE id = :id LIMIT 1");

    $statement->bindValue(':id', $params['id']);

    $result = $statement->execute();

    $post = $result->fetchArray();

    echo phtml('view', ['post' => $post], 'layout');
});

/**
 * Save the form data (posted data) into the database
 */
map('POST', '/', function (SQLite3 $db)
{
    $statement = $db->prepare("INSERT INTO messages (title, content)
    VALUES(:title, :content)");

    $statement->bindValue(':title', $_POST['title']);
    $statement->bindValue(':content', $_POST['content']);

    $statement->execute();

    redirect('/');
});

/**
 * Show a form and pre-fill it with post data by ID
 *
 * Example: /edit/2
 */
map('POST', '/edit/<id>', function ($params, SQLite3 $db)
{
    $statement = $db->prepare("UPDATE messages
    SET title = :title, content = :content
    WHERE id = :id");

    $statement->bindValue(':title', $_POST['title']);
    $statement->bindValue(':content', $_POST['content']);
    $statement->bindValue(':id', $params['id']);

    $statement->execute();

    redirect('/');
});

/**
 * Delete a post from the database by it's ID
 *
 * Example: /destroy/2
 */
map('GET', '/destroy/<id>', function ($params, SQLite3 $db)
{
    $statement = $db->prepare("DELETE FROM messages WHERE id = :id");

    $statement->bindValue(':id', $params['id']);

    $statement->execute();

    redirect('/');
});

# ------------------------------------------------------------------------------

/**
 * Create the database object
 */
$db = new SQLite3('mydb.sqlite3');

/**
 * Create the messages table if it doesn't exist
 */
$query = "CREATE TABLE IF NOT EXISTS messages (
  ID INTEGER,
  title	TEXT,
  content	TEXT,
  PRIMARY KEY(ID)
);";

/**
 * Execute the above query
 */
$db->exec($query);

/**
 * setup the config
 */
config([
    'templates' => './views'
]);

/**
 * Start the application and attach the database to it
 */
dispatch($db);
