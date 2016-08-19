<?php

class summarizer {
	private $ignore = [
		//english
		'carefully', 'correctly', 'eagerly', 'easily', 'fast', 'loudly', 'patiently', 'quickly',
		'quietly', 'well', 'abroad', 'anywhere', 'downstairs', 'here', 'home', 'in', 'nowhere',
		'out', 'outside', 'somewhere', 'there', 'underground', 'upstairs', 'so', 'that', 'to',
		'because', 'since', 'accidentally', 'intentionally', 'purposely', 'always', 'every',
		'never', 'often', 'rarely', 'seldom', 'sometimes', 'usually', 'after', 'already', 'during',
		'finally', 'just', 'last', 'later', 'next', 'now', 'recently', 'soon', 'then', 'tomorrow',
		'when', 'while', 'yesterday', 'everywhere', 'the', 'this', 'these', 'then', 'his', 'her', 'your',
		'from', 'for', 'as', 'at', 'of', 'a', 'an', 'is', 'are', 'and', 'has', 'have', 'on', 'be', 'will',
		'by', 'or', 'been', 'can', 'had', 'not', 'yes',
		//spanish
		'ahora', 'antes', 'después', 'tarde', 'luego', 'ayer', 'temprano', 'ya', 'todavía',
		'anteayer', 'aún', 'pronto', 'hoy', 'aquí', 'ahí', 'allí', 'cerca', 'lejos', 'fuera',
		'dentro', 'alrededor', 'aparte', 'encima', 'debajo', 'delante', 'detrás', 'así',
		'bien', 'mal', 'despacio', 'deprisa', 'como', 'mucho', 'poco', 'muy', 'casi', 'todo',
		'nada', 'algo', 'medio', 'demasiado', 'bastante', 'más', 'menos', 'además', 'incluso',
		'también', 'sí', 'también', 'asimismo', 'no', 'tampoco', 'jamás', 'nunca', 'acaso',
		'quizá', 'quizás', 'el', 'la', 'los', 'las', 'de', 'y', 'es', 'son', 'si', 'no'
	];
	private $words = [];
	private $sentences = [];
	
	private function sanitizer($str) {
		$str = strtolower($str);
		$str = strip_tags(html_entity_decode($str));
		$str = preg_replace('/[^\da-z ]/i', '', $str);
		$str = preg_replace('/\b('.implode('|', $this->ignore).')\b/', '', $str);
		return $str;
	}
	
	function removeFromIgnore($words) {
		$words = strtolower($words);
		$words = explode(' ', $words);
		$this->ignore = array_diff($this->ignore, $words);
	}
	
	function addToIgnore($words) {
		$words = strtolower($words);
		$words = explode(' ', $words);
		$this->ignore = array_merge($this->ignore, $words);
	}
	
	function scoreWords($str) {
		$str = $this->sanitizer($str);
		$words = array_count_values(str_word_count($str, 1));
		if (!empty($words)) {
			$maxRep = max($words);
			foreach ($words as $word => $value) {
				$this->words[$word] = $value/$maxRep;
			}
		}
		arsort($this->words);
	}
	
	function isAnImportantWord($word) {
		if (!empty($this->words[$word])) {
			$score = $this->words[$word];
			return $score >= 0.8;
		}
		return false;
	}
	
	function scoreSentence($sentence) {
		$index = $sentence;
		$sentence = $this->sanitizer($sentence);
		$words = array_count_values(str_word_count($sentence, 1));
		$score = 0;
		$c = 0;
		foreach ($words as $word => $value) {
			if (isset($this->words[$word])) {
				$c++;
				$score += $this->words[$word];
			}
		}
		if ($c > 0) {
			$this->sentences[$index] = $score / $c;
		}
	}
	
	function normSentences() {
		if (!empty($this->sentences)) {
			$maxScore = max($this->sentences);
			foreach ($this->sentences as $sentence => $score) {
				$this->sentences[$sentence] = $score / $maxScore;
			}
		}
	}
	
	function getSentences($content) {
		$paragraphs = explode("\n", $content);
		foreach ($paragraphs as $paragraph) {
			$sentences = explode('. ', $paragraph);
			foreach ($sentences as $sentence) {
				$this->scoreSentence($sentence);
			}
		}
	}
	
	function getAlpha() {
		$values = array_values($this->sentences);
		rsort($values);
		$countDiff = count($values) - 1;
		return $values[$countDiff] + ((1 - $values[$countDiff]) / 3) * (1 - (1 / $countDiff));
	}
	
	function summarize($content, $alpha = null) {
		if (empty($this->words)) $this->scoreWords($content);
		if (empty($this->sentences)) {
			$this->getSentences($content);
		}
		if (!empty($this->sentences)) {
			$summarize = [];
			$this->normSentences();
			$alpha = empty($alpha) ? $this->getAlpha() : $alpha;
			foreach ($this->sentences as $sentence => $score) {
				if ($score > $alpha) {
					$summarize[] = $sentence;
				}
			}
			return implode("\n", $summarize);
		}
		return '';
	}
}
?>