<?php

# Composer autoloader
require_once 'vendor/autoload.php';

# ------------------------------------------------------------------------------

# Messages overview
map('GET', '/', function ($db)
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

# View a single message
map('GET', '/<id>', function($params, $db)
{
  $statement = $db->prepare("SELECT * FROM messages WHERE id = :id LIMIT 1");
  $statement->bindValue(':id', $params['id']);

  $result = $statement->execute();

  $post = $result->fetchArray();

  echo phtml('view', ['post' => $post], 'layout');
});

# New message form
map('GET', '/new', function ()
{
  echo phtml('new', ['action' => '/'], 'layout');
});

# Save new message to database
map('POST', '/', function ($db)
{
  $statement = $db->prepare("INSERT INTO messages (title, content)
    VALUES(:title, :content)");

  $statement->bindValue(':title', $_POST['title']);
  $statement->bindValue(':content', $_POST['content']);

  $statement->execute();

  return redirect('/');
});

# Edit message form
map('GET', '/edit/<id>', function ($params, $db)
{
  $statement = $db->prepare("SELECT * FROM messages WHERE id = :id LIMIT 1");
  $statement->bindValue(':id', $params['id']);

  $result = $statement->execute();

  $post = $result->fetchArray();

  echo phtml('edit', [
    'action' => '/edit/' + $params['id'],
    'post'   => $post
  ], 'layout');
});

# Save editted message to database
map('POST', '/edit/<id>', function ($params, $db)
{
  $statement = $db->prepare("UPDATE messages
    SET title = :title, content = :content
    WHERE id = :id");

  $statement->bindValue(':title', $_POST['title']);
  $statement->bindValue(':content', $_POST['content']);
  $statement->bindValue(':id', $params['id']);

  $statement->execute();

  return redirect('/');
});

map('GET', '/destroy/<id>', function ($params, $db)
{
  $statement = $db->prepare("DELETE FROM messages WHERE id = :id");

  $statement->bindValue(':id', $params['id']);

  $statement->execute();

  return redirect('/');
});

# ------------------------------------------------------------------------------

# Create the database object
$db = new SQLite3('mydb.sqlite3');

# Create the messages table if it doesn't exist
$query = "CREATE TABLE IF NOT EXISTS messages (
  ID INTEGER,
  title	TEXT,
  content	TEXT,
  PRIMARY KEY(ID)
);";

$db->exec($query);

# setup the config
config([
  'templates' => './views'
]);

# Start the application
dispatch($db);
