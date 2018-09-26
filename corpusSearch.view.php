<!doctype html>
<html>
<head>
	<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

	<title><?php echo !empty($search) ? 'Search: ' . htmlentities($search) : 'Viana Translated Swahili Corpus' ?></title>
	<style>
	body {
		font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
		line-height: 125%;
		color: #333;
	}
	.main {
		max-width: 100%;
		margin: 0 auto;
	}
	h1 {
		font-size: 1.5em;
		font-weight: normal;
		line-height: 135%;
	}
		h1 form {
			display: inline-block;
			margin: 0;
		}
	.searchResults {
		list-style: none;
		margin: 0;
		padding: 0;
	}
	.resultItem {
		margin-bottom: 2em;
	}
		.resultItem h2 {
			font-weight: normal;
			font-size: 1.2em;
			margin-bottom: .3%;
		}
		.resultItem h2 date {
			font-size: .7em;
			color: gray;
			border: 1px solid gray;
			border-radius: .2em;
			padding: .1em .2em;
		}
		.resultItem .metadata {
			margin: 0;
			padding: 0;
			list-style: none;
			font-size: .9em;
			color: #555;
		}
		.resultItem .text-lines {
			border-collapse: collapse;
			margin-top: 1em;
		}
		.resultItem .text-lines th {
			padding-bottom: .5em;
			border: none;
			text-align: left;
			border-bottom: 2px solid #888;
		}
			.resultItem .text-lines th .original {
				background: #60e860;
				color: white;
				border-radius: .3em;
				padding: .2em .3em;
				font-size: .8em;
				font-weight: normal;
			}
		.resultItem .text-lines td {
			padding: .5%;
			background: white;
			border-left: .4em solid white;
			border-bottom: .5em solid white;
			vertical-align: top;
			font-size: .9em;
			background: #f8f8f8;
			line-height: 125%;
		}
		.resultItem .text-lines td:first-child {
			border-left: none;
		}
			.resultItem .text-lines td em {
				background: #fff772;
				border-radius: .1em;
				font-style: normal;
			}
			.resultItem .text-lines td .not-translated {
				color: gray;
			}

		footer {
			color: #555;
			margin: 2em 0 .5em;
			border-top: 3px solid #e1e1e1;
		}
	</style>
</head>
<body>
	<article class="main">

		<h1>
			<?php if (empty($_POST)) : ?>
			Search the Viana Translated Swahili Corpus: 
			<?php else : ?>
			<strong><?php echo $totalOcurrences ?> ocurrences in <?php echo count($linesByDoc) ?> texts</strong> 
			for 
			<?php endif ?>
			<form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
				<input name="search" value="<?php echo $search ?? '' ?>" autofocus>
				<button type="submit" name="sent">&#128269;</button>
			</form>
		</h1>
		
		<?php if (!empty($linesByDoc)) : ?>
		<ol class="searchResults">
		<?php foreach ($linesByDoc as $result) : ?>
			<li class="resultItem">
				
				<h2 class="title">
					<?php echo $result['metadata']['title'] ?>
				</h2>

				<ul class="metadata">
					<li>Author: <?php echo $result['metadata']['author'] ?></li>
					<li>Original language: <?php echo $allLanguages[$result['metadata']['id_lang_original']]['name'] ?></li>
				</ul>
				
				<table class="text-lines">
					<thead>
						<tr>
						<?php foreach ($result['languages'] as $idLang) : ?>
							<th width="<?php echo (100 / count($result['languages'])) ?>%">
								<?php echo $allLanguages[$idLang]['name'] ?>
								<?php if ($idLang == $result['metadata']['id_lang_original']) : ?>
									<span class="original">original</span>
								<?php endif ?>
							</th>
						<?php endforeach ?>
						</tr>
					</thead>
					<tbody>
					<?php foreach ($result['lines'] as $currLine) : ?>
						<tr>
						<?php foreach ($result['languages'] as $currentLang) : ?>
							<td><?php echo $currLine[$currentLang] ?? '<span class="not-translated">(not translated)</span>' ?></td>
						<?php endforeach ?>
						</tr>
					<?php endforeach ?>
					</tbody>
				</table>

			</li>
		<?php endforeach ?>
		</ol>
		<?php endif ?>
	</article>

	<footer>
		<h3>What is this?</h3>
		<p>The <strong>Viana Translated Swahili Corpus</strong> is a collection of texts translated into Swahili from different original languages by trusted translators. It allows you to look for <em>actual</em> translations of the words you need.</p>
	</footer>
</body>
</html>