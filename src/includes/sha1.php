<?php

/**
 * @file
 * SHA class for sermepa connections not "Enhanced SHA"
 */

class SHA1 {
  // Result variables.
  var $A, $B, $C, $D, $E;
  // Temp variables.
  var $ta, $tb, $tc, $td, $te;
  var $K0_19, $K20_39, $K40_59, $K60_79;

  var $buffer;
  var $buffsize;
  var $totalsize;

  function SHA() {
    $this->init();
  }

  function init() {
    // The long constants can't be used for some dumb reason.
    $this->A = 0x6745 << 16 | 0x2301;
    $this->B = 0xefcd << 16 | 0xab89;
    $this->C = 0x98ba << 16 | 0xdcfe;
    $this->D = 0x1032 << 16 | 0x5476;
    $this->E = 0xc3d2 << 16 | 0xe1f0;
    $this->ta = $this->A;
    $this->tb = $this->B;
    $this->tc = $this->C;
    $this->td = $this->D;
    $this->te = $this->E;
    $this->K0_19 = 0x5a82 << 16 | 0x7999;
    $this->K20_39 = 0x6ed9 << 16 | 0xeba1;
    $this->K40_59 = 0x8f1b << 16 | 0xbcdc;
    $this->K60_79 = 0xca62 << 16 | 0xc1d6;

    $this->buffer = array();
    $this->buffsize = 0;
    $this->totalsize = 0;
  }

  function bytes_to_words($block) {
    $nblk = array();
    for ($i = 0; $i < 16; ++$i) {
      $index = $i * 4;
      $nblk[$i] = 0;
      $nblk[$i] |= ($block[$index] & 0xff) << 24;
      $nblk[$i] |= ($block[$index + 1] & 0xff) << 16;
      $nblk[$i] |= ($block[$index + 2] & 0xff) << 8;
      $nblk[$i] |= ($block[$index + 3] & 0xff);
    }
    return $nblk;
  }

  function pad_block($block, $size) {
    // Returns a block that is a multiple of 512 bits long.
    $blksize = sizeof($block);
    $bits = $size * 8;

    // Always pad with 0x80, then add as many zeros as necessary to
    // make the message 64 bits short of 512.  Then add the 64-bit size.
    $newblock = $block;
    $newblock[] = 0x80;
    // Add the zeros.
    while ((sizeof($newblock) % 64) != 56) {
      $newblock[] = 0;
    }
    // Add the size.
    for ($i = 0; $i < 8; ++$i) {
      $newblock[] = ($i < 4) ? 0 : ($bits >> ((7 - $i) * 8)) &0xff;
    }

    return $newblock;
  }

  function circ_shl($num, $amt) {
    $leftmask = 0xffff | (0xffff << 16);
    $leftmask <<= 32 - $amt;
    $rightmask = 0xffff | (0xffff << 16);
    $rightmask <<= $amt;
    $rightmask = ~$rightmask;

    $remains = $num & $leftmask;
    $remains >>= 32 - $amt;
    $remains &= $rightmask;

    $res = ($num << $amt) | $remains;

    return $res;
  }

  function f0_19($x, $y, $z) {
    return ($x & $y) | (~$x & $z);
  }

  function f20_39($x, $y, $z) {
    return ($x ^ $y ^ $z);
  }

  function f40_59($x, $y, $z) {
    return ($x & $y) | ($x & $z) | ($y & $z);
  }

  function f60_79($x, $y, $z) {
    return $this->f20_39($x, $y, $z);
  }

  function expand_block($block) {
    $nblk = $block;
    for ($i = 16; $i < 80; ++$i) {
      $nblk[$i] = $this->circ_shl(
           $nblk[$i - 3] ^ $nblk[$i - 8] ^ $nblk[$i - 14] ^ $nblk[$i - 16], 1
        );
    }

    return $nblk;
  }

  function print_bytes($bytes) {
    $len = sizeof($bytes);
    for ($i = 0; $i < $len; ++$i) {
      $str[] = sprintf("%02x", $bytes[$i]);
    }

    print (join(", ", $str) . "\n");
  }

  function wordstr($word) {
    return sprintf(
      "%04x%04x", ($word >> 16) & 0xffff, $word & 0xffff
    );
  }

  function print_words($words) {
    $len = sizeof($words);
    for ($i = 0; $i < $len; ++$i) {
      $str[] = $this->wordstr($words[$i]);
    }

    print (join(", ", $str) . "\n");
  }

  function hash_to_string($hash) {
    $astr = '';
    $len = sizeof($hash);
    for ($i = 0; $i < $len; ++$i) {
      $astr .= $this->wordstr($hash[$i]);
    }
    return $astr;
  }

  // Add simply adds two numbers.  It is provided for compatibility on
  // platforms that only support a 31 bit add (there are a few, apparently).
  function add($a, $b) {
    $ma = ($a >> 16) & 0xffff;
    $la = ($a) & 0xffff;
    $mb = ($b >> 16) & 0xffff;
    $lb = ($b) & 0xffff;

    $ls = $la + $lb;
    // Carry.
    if ($ls > 0xffff) {
      $ma += 1;
      $ls &= 0xffff;
    }

    // MS add.
    $ms = $ma + $mb;
    $ms &= 0xffff;

    // Works because the bitwise operators are 32 bit
    $result = ($ms << 16) | $ls;
    return $result;
  }

  function process_block($blk) {
    $blk = $this->expand_block($blk);

    for ($i = 0; $i < 80; ++$i) {
      $temp = $this->circ_shl($this->ta, 5);
      if ($i < 20) {
        $f = $this->f0_19($this->tb, $this->tc, $this->td);
        $k = $this->K0_19;
      }
      elseif ($i < 40) {
        $f = $this->f20_39($this->tb, $this->tc, $this->td);
        $k = $this->K20_39;
      }
      elseif ($i < 60) {
        $f = $this->f40_59($this->tb, $this->tc, $this->td);
        $k = $this->K40_59;
      }
      else {
        $f = $this->f60_79($this->tb, $this->tc, $this->td);
        $k = $this->K60_79;
      }

      $temp = $this->add($temp, $f);
      $temp = $this->add($temp, $this->te);
      $temp = $this->add($temp, $blk[$i]);
      $temp = $this->add($temp, $k);

      $this->te = $this->td;
      $this->td = $this->tc;
      $this->tc = $this->circ_shl($this->tb, 30);
      $this->tb = $this->ta;
      $this->ta = $temp;
    }

    $this->A = $this->add($this->A, $this->ta);
    $this->B = $this->add($this->B, $this->tb);
    $this->C = $this->add($this->C, $this->tc);
    $this->D = $this->add($this->D, $this->td);
    $this->E = $this->add($this->E, $this->te);
  }

  // Process each full block.
  function update($bytes) {
    $length = sizeof($bytes);
    $index = 0;

    while (($length - $index) + $this->buffsize >= 64) {
      for ($i = $this->buffsize; $i < 64; ++$i) {
        $this->buffer[$i] = $bytes[$index + $i - $this->buffsize];
      }
      $this->process_block($this->bytes_to_words($this->buffer));
      $index += 64;
      $this->buffsize = 0;
    }

    // Any remaining bytes that do not make up a full block need to be
    // added into the buffer for the next update (or finalize).
    $remaining = $length - $index;
    for ($i = 0; $i < $remaining; ++$i) {
      $this->buffer[$this->buffsize + $i] = $bytes[$index + $i];
    }
    $this->buffsize += $remaining;
    $this->totalsize += $length;
  }

  function finale() {
    // Pad and process the buffer.
    for ($i = 0; $i < $this->buffsize; ++$i) {
      $last_block[$i] = $this->buffer[$i];
    }
    $this->buffsize = 0;
    // Pad the block.
    $last_block = $this->pad_block($last_block, $this->totalsize);
    // Process the last one (or two) block(s).
    $index = 0;
    $length = sizeof($last_block);
    while ($index < $length) {
      $block = array();
      for ($i = 0; $i < 64; ++$i) {
        $block[$i] = $last_block[$i + $index];
      }
      $this->process_block($this->bytes_to_words($block));
      $index += 64;
    }

    $result[0] = $this->A;
    $result[1] = $this->B;
    $result[2] = $this->C;
    $result[3] = $this->D;
    $result[4] = $this->E;

    return $result;
  }

  function hash_bytes($bytes) {
    $this->init();
    $this->update($bytes);
    return $this->finale();
  }

  function hash_string($str) {
    $len = strlen($str);
    for ($i = 0; $i < $len; ++$i) {
      $bytes[] = ord($str[$i]) & 0xff;
    }
    return $this->hash_bytes($bytes);
  }
}
