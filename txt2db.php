<?php

$conn = new mysqli("localhost", "root", "root", "VianaCorpus");
$conn->set_charset('utf8');

if (!isset($argv[3]))
{
	die("You must specify 3 params: document ID, language code and file name.\n");
}

$id_document 	= $argv[1];
$lang_code 		= $argv[2];
$file 			= $argv[3];

$stmt 			= $conn->query("SELECT id_lang, code FROM lang");
$languages 		= [];

while ($row = $stmt->fetch_assoc())
{
	$languages[$row['code']] = $row['id_lang'];
}

if (!isset($languages[$lang_code]))
{
	die("Error: the language code you specified does not exist in the DB.\n");
}

if ((string) intval($id_document) !== $id_document)
{
	die("Error: the document ID must be an integer number.\n");
}

$stmt = $conn->query("SELECT id_document FROM document WHERE id_document = '$id_document'");

if (!$conn->affected_rows)
{
	die("Error: document #$id_document does not exist.\n");
}

$stmt = $conn->query("SELECT `number` FROM textline WHERE id_document = '$id_document' AND id_lang = " . $languages[$lang_code]);

if ($conn->affected_rows)
{
	die("Error: document #$id_document already has text in the '$lang_code' language. Please remove it if you want to change it.\n");
}

if (!file_exists($file))
{
	die("Error: the filename specified does not exist.\n");
}

$lines = file($file);

$inserted_lines = 0;
foreach ($lines as $number=>$line)
{
	$line = trim($line);

	if ('#' != $line)
	{
		$conn->query(
			"INSERT INTO textline (id_document, `number`, id_lang, contents) VALUES ("
			. "'$id_document', "
			. "'" . ($number + 1) . "', " // +1 because count starts from zero.
			. "'" . $languages[$lang_code] . "', "
			. "'" . $conn->real_escape_string($line) . "')"
		);

		$inserted_lines++;

	}
}

echo "$inserted_lines out of " . count($lines) . " inserted OK.\n";