# exercise_dna_sequence_match
<h2>PHP Exercise - DNA Sequence Match</h2>
<em>This was a code exercise requested for a role.  I did not want the code to go to waste, so I'm releasing it here to assist anyone who might be trying to solve similar issues.</em><br />
<strong>I am not a geneticist, nor have I played one on TV.  Please do not throw this logic into your fancy new CRISPR machine!</strong>

<h3>Exercise Goals</h3>
(Quoted from original requirements)
<blockquote>
DNA sequences are strings which contain only the letters A, C, G, and T.  Two sequences are considered akin if they both contain sub-sequences which form an anagram of each other (e.g. ACG, GAC, GCA, and etc.).

In two given DNA sequences find the largest possible sub-sequence that would be considered akin.  Output the length of the akin sub-sequence and it's position (0-based) in both input sequences.

<strong>Examples</strong>
<table>
  <tr>
    <th>Input</th><th>Result</th>
  </tr>
  <tr>
    <td>ACGT (first sequence)<br />GTC(second sequence)</td>
    <td>
      3 (length)<br />
      1 (position in first sequence)<br />
      0 (position in second sequence)<br />
    </td>
  </tr>
  <tr>
    <td>ACGT<br />CAT</td>
    <td>2<br />0<br />0<br /></td>
  </tr>
  <tr>
    <td>ACA<br />AC<br/>&nbsp;</td>
    <td>2<br />0<br />0</td>
  </tr>
  <tr>
    <td>ACA<br />GT<br />&nbsp;</td>
    <td>0<br />-1<br />-1</td>
  </tr>
</table>
</blockquote>
