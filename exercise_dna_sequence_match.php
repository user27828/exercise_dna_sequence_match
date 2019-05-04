#!/usr/bin/php
<?PHP
/**
 *	Exercise algorithm: DNA sub-sequence matching
 *	Objectives:
 *		1. Accept 2 DNA sequence strings containing nucleotides of A, C, G, and T
 *		2. Find common anagrams in each pair
 *		3. Output the largest matching pair with results showing:
 *			A) Pair length
 *			B) Position in first sequence
 *			C) Position in second sequence
 * 
 *	Usage: PHP CLI - ensure permissions to execute on a *nix system.  Modify #!/usr/bin/php if needed.
 *		(with #! php path):		<path-to-file>/thisfile.php
 *		(without #!):			php <path-to-file>/thisfile.php
 *
 *	Arguments: 1 - <comma-delimited sequence pair>[ Additional pairs]
 *		<path-to-file>/thisfile.php TACG,GC
 *		<path-to-file>/thisfile.php TACG,GC TTGACATG,AGTAGCAT
 *	
 *	Limitations:	<total character combinations of smaller seq> <= PHP_INT_MAX
 *
 *	@author Marc Stephenson / GitHub: user27828
 */
final class DNA_Largest_Pair {
	private		$_debug				= TRUE;		// Debug turns on printing/filtering of the grammatical numbers
	private		$_debug_seq_perms	= FALSE;	// Debug the sequence permutations (can get lengthy!)
	private		$_PAIRS				= array(
		// These defined pairs are from the exercise sample and can be overridden via CLI args
		array('ACGT', 'GTC'),
		array('ACGT', 'CAT'),
		array('ACA', 'AC'),
		array('ACA', 'GT'),
	);

	/**
	 *	Constructor
	 */
	public function __construct() {
		$this->echo_status('+ Find DNA sequences with anagram pairs within them, output largest result per pair.');
		
		// Accept pairs from command line in format: thisfile.php <pair1a>,<pair1b>[ <pair2a>,<pair2b>...]
		if( isset($GLOBALS['argv']) && count($GLOBALS['argv'])>1 ) {
			$DATA		= array();
			$PAIRS		= array_slice($GLOBALS['argv'], 1);
			foreach($PAIRS as $i => $pair) {
				$pair		= strtoupper($pair);
				if( preg_match('/(?P<PAIR_A>[acgt]+)\,(?P<PAIR_B>[acgt]+)/is', $pair, $REGS) ) {
					if( $this->_debug ) {
						$this->echo_status(sprintf('CLI Pair %d: %s | %s', $i, $REGS['PAIR_A'], $REGS['PAIR_B']));
					}
					$DATA[]		= array($REGS['PAIR_A'], $REGS['PAIR_B']);
				} else {
					$this->echo_status(sprintf('+ERROR: Invalid pair string: "%s"', $pair));
				}	// End IF/ELSE
			}
			if( !empty($DATA) ) {
				$this->_PAIRS		= $DATA;
			}
		}
		return $this->run();
	}

	/**
	 *	Output status
	 * @param string|array	$status		- Status message
	 * @param boolean 		$exit		- Exit script after printing status?
	 */
	public function echo_status($status, $exit=FALSE) {
		$status		= !is_array($status) ? $status : implode("\n", $status);
		echo $status . "\n";
		if( $exit ) {
			echo "Quitting...\n\n";
			exit;
		}
	}

	/**
	 *	Get all fixed and variable length sequences.
	 * Merges results from get_sequences_fixed() and get_sequences_variable()
	 * @param string|array $str		- Nucleotides
	 * @return array				- All fixed-length sequences
	 */
	public function get_sequences_all($str) {
		return array_merge($this->get_sequences_fixed($str), $this->get_sequences_variable($str));
	}
	
	/**
	 *	Get all possible sequences for a given string
	 * Expected results are: strlen! (permutation of string length)
	 * @param string|array $str		- Nucleotides
	 * @return array				- All fixed-length sequences
	 */
	public function get_sequences_fixed($str) {
		$DATA	= array();
		$STR	= is_array($str) ? $str : str_split($str);
		if( count($STR)==1 ) { return $STR; }
		foreach($STR as $k => $letter) {
			$current_cmp	= array($k => $letter);					// Current k/v pair 
			// Recursion list of everything but current k/v.  array_diff_key() is required because
			// nucleotides can repeat in sequences
			$STR_RECURSE	= $this->get_sequences_fixed(array_diff_key($STR, $current_cmp));
			foreach($STR_RECURSE as $str_sub) {
				$DATA[]			= $letter . $str_sub;
			}
		}
		return $DATA;
	}

	/**
	 * Get all variable-length sequences.  Start with leading char removals, followed by trailing
	 * @param string|array $str		- Nucleotides
	 * @return array				- All variable-length sequences
	 */
	public function get_sequences_variable($str) {
		$DATA	= array();
		$STR	= is_array($str) ? $str : str_split($str);
		// Leading char removals
		for($i=1; $i<count($STR); $i++) {
			$STRV		= array_slice($STR, $i);
			$RES		= $this->get_sequences_fixed($STRV);
			if( count($RES)>1 ) {
				$DATA	= array_merge($DATA, $RES);
			}
		}

		// Trailing char removals
		for($i=count($STR)-1; $i>=0; $i--) {
			$STRV		= array_slice($STR, 0, $i);
			$RES		= $this->get_sequences_fixed($STRV);
			if( count($RES)>1 ) {
				$DATA	= array_merge($DATA, $RES);
			}
		}
		return $DATA;
	}
	
	/**
	 *	Find sequence match
	 *	If you really wanted to get more efficient, you can do all comparison with binary math,
	 *	this is quick & dirty.
	 * @param string $seq_1		- DNA sequence 1
	 * @param string $seq_2		- DNA sequence 2
	 * @return array(
	 *		'length'		=> (integer)<match length>,
	 *		'seq_1_pos'		=> (integer)<sequence 1 pos|-1>,
	 *		'seq_2_pos'		=> (integer)<sequence 1 pos|-1>,
	 * )
	 */
	public function find_match($seq1, $seq2) {
		$len		= null;
		$seq1_pos	= null;
		$seq2_pos	= null;
		if( $seq1 == $seq2 ) {	// That was easy!
			$len		= strlen($seq1);
			$seq1_pos	= 0;
			$seq2_pos	= 0;
		} else {
			// Assignments based on string length - we only need to create sequences for the shorter
			// string for comparison.
			if( strlen($seq1) > strlen($seq2) ) {
				// Longer string is seq1
				$long		= $seq1;
				$short		= $seq2;
				$seq1		=& $long;
				$seq2		=& $short;
			} else {
				// Longer string is seq2 OR
				// This will also match if they are equal length, in which case, it doesn't matter
				$long		= $seq2;
				$short		= $seq1;
				$seq1		=& $short;
				$seq2		=& $long;
			}

			if( ($pos=strpos($long, $short))!==FALSE || ($pos=strpos($long, strrev($short)))!==FALSE ) {
				// Literal sub-string comparisons
				// Is the short string (or it's reverse) a literal substring of the larger one?
				$len		= strlen($short);
				$seq1_pos	= $pos;
				$seq2_pos	= 0;	// seq 2 is the shorter substring in seq 1
			} else {
				// Iterate through the "short" sequence only, 
				// and compare all variations of it to the "long" sequence
				$SHORT_STRINGS	= $this->get_sequences_all($short);
				if( $this->_debug_seq_perms ) {
					// Dump all sequences
					$this->echo_status('Dumping ALL sequences - ' . print_r($SHORT_STRINGS,1));
				}
				$pos	= null;
				foreach($SHORT_STRINGS as $str_short) {
					if( ($pos=strpos($long, $str_short))!==FALSE ) {
						$len		= strlen($str_short);
						$seq1_pos	= ($seq1===$short) ? 0 : strpos($seq1, $str_short);
						$seq2_pos	= ($seq2===$short) ? 0 : strpos($seq2, $str_short);
						break;
					}
				}

				// There was no $pos found in the loop above, so the sequence is not present in the parent
				if( is_null($pos) || $pos===FALSE ) {
					$len		= 0;
					$seq1_pos	= -1;
					$seq2_pos	= -1;
				}
			}	// End IF/ELSE - String comparisons
		}
		return array(
			'length'		=> $len,
			'seq1_pos'		=> $seq1_pos!==FALSE ? $seq1_pos : -1,
			'seq2_pos'		=> $seq2_pos!==FALSE ? $seq2_pos : -1
		);
	}

	/**
	 *	Main
	 */
	public function run() {
		foreach($this->_PAIRS as $k => $PAIR) {
			$RES			= $this->find_match($PAIR[0], $PAIR[1]);
			$this->echo_status(sprintf('Pair:            %s / %s', $PAIR[0], $PAIR[1]));
			$this->echo_status(sprintf('Match Length:    %d', $RES['length']));
			$this->echo_status(sprintf('Sequence 1 pos:  %d', $RES['seq1_pos']));
			$this->echo_status(sprintf('Sequence 2 pos:  %d', $RES['seq2_pos']));
			$this->echo_status('--');
		}
		$this->echo_status(sprintf('+Total Sequences Checked:           %d', count($this->_PAIRS)));
		$this->echo_status('', TRUE);	// Quit
	}
}	// End Class DNA_Largest_Pair

// Instantiate class.  It will auto-run via class constructor
$Run	= new DNA_Largest_Pair();
?>