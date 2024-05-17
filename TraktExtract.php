<?php

/* 

 * Script Name: TraktExtract
 * Description: This script uses cURL to make a request to extract your personal info from Trakt.
 * Author: EdEquinox
 * Author URI: https://github.com/EdEquinox
 * Version: 1.0.0
 
*/

// create a Trakt app to get a client API key: http://docs.trakt.apiary.io/#introduction/create-an-app
// enter your username and API key below

echo "Enter your username: ";
$username = trim(fgets(STDIN));

echo "Enter your API key: ";
$apikey = trim(fgets(STDIN));

$zip = new ZipArchive();
$zip_filename = "trakt_backup_" . date("Y-m-d") . ".zip";
$zip_filepath = "C:\\tools\\trakt_backup_" . date("Y-m-d") . ".zip";

if ($zip->open($zip_filepath, ZipArchive::CREATE) !== TRUE) {
  exit("Cannot open <$zip_filepath>\n");
}

echo "\n\nLoading...";

loadAndZip("watchlist/movies/", "watchlist_movies.json");
loadAndZip("watchlist/shows/", "watchlist_shows.json");
loadAndZip("watchlist/episodes/", "watchlist_episodes.json");
loadAndZip("watchlist/seasons/", "watchlist_seasons.json");

loadAndZip("ratings/movies/", "ratings_movies.json");
loadAndZip("ratings/shows/", "ratings_shows.json");
loadAndZip("ratings/episodes/", "ratings_episodes.json");
loadAndZip("ratings/seasons/", "ratings_seasons.json");

loadAndZip("collection/movies/", "library_collection_movies.json");
loadAndZip("collection/shows/", "library_collection_shows.json");
loadAndZip("watched/movies/", "watched_movies.json");
loadAndZip("watched/shows/", "watched_shows.json");

loadAndZip("history/movies/", "history_movies.json");
loadAndZip("history/shows/", "history_shows.json");

loadAndZip("/comments/all/movies?include_replies=false", "movies_comments.json");
loadAndZip("/comments/all/shows?include_replies=false", "shows_comments.json");
loadAndZip("/comments/all/seasons?include_replies=false", "seasons_comments.json");
loadAndZip("/comments/all/episodes?include_replies=false", "episodes_comments.json");
loadAndZip("/comments/all/lists?include_replies=false", "lists_comments.json");

echo "\n\n\nData succesefully extracted!\n\n";

$zip->close();



exit();

function flatten($array) {
  $result = array();
  foreach ($array as $key => $value) {
      if (is_array($value)) {
          $result = array_merge($result, flatten($value));
      } else {
          $result[$key] = $value;
      }
  }
  return $result;
}

function loadAndZip($path, $filename)
{
  global $zip, $apikey, $username;

  $url = "https://api.trakt.tv/users/" . $username . '/' . $path  ;
  $ch = curl_init();
  curl_setopt_array($ch, array(
      CURLOPT_URL => $url,
      CURLOPT_HTTPHEADER => array(
          "Content-Type: application/json",
          "trakt-api-key: " . $apikey,
          "trakt-api-version: 2"),
      //CURLOPT_VERBOSE => true,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_SSL_VERIFYPEER => 0,
      CURLOPT_SSL_VERIFYHOST => 0
  ));

  $result = curl_exec($ch);

  echo ".";

  $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  if($httpCode == 404) {
    exit("<h3>Wrong username!</h3>");
  }
  // Decode JSON data to PHP array
  $data = json_decode($result, true);

  // Get the filename without the extension
  $filenameWithoutExtension = pathinfo($filename, PATHINFO_FILENAME);

  // Open a file for writing
  $fp = fopen($filenameWithoutExtension . '.csv', 'w');

  // Get the headers of the file
  if(!empty($data)) {
      fputcsv($fp, array_keys($data[0]));
  }

  // Loop through the array and write each line to the CSV file
  foreach ($data as $row) {
    fputcsv($fp, flatten($row));
  }

  // Close the file
  fclose($fp);

  // Add the CSV file to the zip
  $zip->addFile($filenameWithoutExtension . '.csv');
}
