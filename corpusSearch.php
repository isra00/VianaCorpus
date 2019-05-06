<?php

//error_reporting(E_ERROR);
ini_set("display_errors" , true);

$conn = new mysqli("localhost", "isra", "isra", "VianaCorpus");
$conn->set_charset('utf8');

$stmtCountDocuments = $conn->query('SELECT COUNT(*) as docs FROM document');
$totalDocs = $stmtCountDocuments->fetch_assoc()['docs'];
$stmtCountLangs = $conn->query('SELECT COUNT(DISTINCT id_lang) langs FROM textline');
$totalLangs = $stmtCountLangs->fetch_assoc()['langs'];

$stmtAvailableLangs = $conn->query('SELECT DISTINCT id_lang, name FROM `textline` JOIN lang USING (id_lang) LIMIT 50');
$availableLangs = [];
while ($lang = $stmtAvailableLangs->fetch_assoc())
{
	$availableLangs[] = $lang['name'];
}

$lastLang = array_pop($availableLangs);
$availableLangs = join($availableLangs, ', ') . ' or ' . $lastLang;

if (empty($_POST)) {
	goto view;
}

$search = $_POST['search'];

// Simply get lines with the search string

$sql = <<<SQL
SELECT *
FROM textline
WHERE contents LIKE '%$search%'
ORDER BY `number`
SQL;

$sqlResult = $conn->query($sql);

$linesByDoc = [];
$totalOcurrences = $sqlResult->num_rows;

// Group lines by Doc + inflate (fetch translations of those lines)

while ($row = $sqlResult->fetch_assoc())
{
	if (!isset($linesByDoc[$row['id_document']]))
	{
		$linesByDoc[$row['id_document']] = ['lines' => [], 'languages' => []];
	}

	if (!isset($linesByDoc[$row['id_document']]['lines'][$row['number']]))
	{
		$linesByDoc[$row['id_document']]['lines'][$row['number']] = [];
	}

	$linesByDoc[$row['id_document']]['lines'][$row['number']][$row['id_lang']] = $row['contents'];
	$linesByDoc[$row['id_document']]['languages'][$row['id_lang']] = true;

	$stmt = $conn->query("
		SELECT *
		FROM textline
		WHERE id_document = '" . $row['id_document'] . "'
		AND `number` = '" . $row['number'] . "'
		AND NOT id_lang = '" . $row['id_lang'] . "'
	");

	while ($transRow = $stmt->fetch_assoc())
	{
		$linesByDoc[$transRow['id_document']]['lines'][$transRow['number']][$transRow['id_lang']] = $transRow['contents'];
		$linesByDoc[$transRow['id_document']]['languages'][$transRow['id_lang']] = true;
	}
}

if (!$linesByDoc) goto view;

// Get document metadata + final operations

$stmt = $conn->query(
	"SELECT * FROM document WHERE id_document IN ("
	. implode(', ', array_keys($linesByDoc))
	. ") ORDER BY publish_year, publish_month, publish_day"
);

$foundDocs = [];

while ($docsRow = $stmt->fetch_assoc())
{
	$linesByDoc[$docsRow['id_document']]['metadata'] 		= $docsRow;
	$linesByDoc[$docsRow['id_document']]['languages'] 		= array_keys($linesByDoc[$docsRow['id_document']]['languages']);

	foreach ($linesByDoc[$docsRow['id_document']]['lines'] as &$translations)
	{
		foreach ($translations as &$translatedLine)
		{
			//Operations for the interface
			$translatedLine = preg_replace("/($search)/i", "<em>$1</em>", $translatedLine);
		}
	}

	$niceTitle = $linesByDoc[$docsRow['id_document']]['metadata']['title'] . ' <date>';

	if ($linesByDoc[$docsRow['id_document']]['metadata']['publish_day']) {
		$niceTitle .= $linesByDoc[$docsRow['id_document']]['metadata']['publish_day'] . '/';
	}
	if ($linesByDoc[$docsRow['id_document']]['metadata']['publish_month']) {
		$niceTitle .= $linesByDoc[$docsRow['id_document']]['metadata']['publish_month'] . '/';
	}
	if ($linesByDoc[$docsRow['id_document']]['metadata']['publish_year']) {
		$niceTitle .= $linesByDoc[$docsRow['id_document']]['metadata']['publish_year'];
	}

	$niceTitle .= '</date>';

	$linesByDoc[$docsRow['id_document']]['metadata']['title'] = $niceTitle;
}

$stmt 			= $conn->query("SELECT id_lang, code, name FROM lang");
$allLanguages 	= [];

while ($row = $stmt->fetch_assoc())
{
	$allLanguages[$row['id_lang']] = $row;
}

view:

include 'corpusSearch.view.php';