<?php
/*
 * Session Management for PHP3
 *
 * (C) Copyright 1999-2000 NetUSE GmbH
 *                    Kristian Koehntopp
 *
 * $Id$
 *
 * This code was NOT written by the phpBB group. It is part of the PHPLib 
 * package written by NetUSE GmbH and Kristian Koehntopp.
 * This code is released under the GNU General Public Licence and used in
 * accordance with said licence.
 */ 

class Template {
  var $classname = "Template";

  /* if set, echo assignments */
  var $debug     = false;

  /* $file[handle] = "filename"; */
  var $file  = array();

  /* relative filenames are relative to this pathname */
  var $root   = "";

  /* $varkeys[key] = "key"; $varvals[key] = "value"; */
  var $varkeys = array();
  var $varvals = array();

  /* "remove"  => remove undefined variables
   * "comment" => replace undefined variables with comments
   * "keep"    => keep undefined variables
   */
  var $unknowns = "remove";
  
  /* "yes" => halt, "report" => report error, continue, "no" => ignore error quietly */
  var $halt_on_error  = "yes";
  
  /* last error message is retained here */
  var $last_error     = "";


  /***************************************************************************/
  /* public: Constructor.
   * root:     template directory.
   * unknowns: how to handle unknown variables.
   */
  function Template($root = ".", $unknowns = "remove") {
    $this->set_root($root);
    $this->set_unknowns($unknowns);
  }

  /* public: setroot(pathname $root)
   * root:   new template directory.
   */  
  function set_root($root) {
    if (!is_dir($root)) {
      $this->halt("set_root: $root is not a directory.");
      return false;
    }
    
    $this->root = $root;
    return true;
  }

  /* public: set_unknowns(enum $unknowns)
   * unknowns: "remove", "comment", "keep"
   *
   */
  function set_unknowns($unknowns = "keep") {
    $this->unknowns = $unknowns;
  }

  /* public: set_file(array $filelist)
   * filelist: array of handle, filename pairs.
   *
   * public: set_file(string $handle, string $filename)
   * handle: handle for a filename,
   * filename: name of template file
   */
  function set_file($handle, $filename = "") {
    if (!is_array($handle)) {
      if ($filename == "") {
        $this->halt("set_file: For handle $handle filename is empty.");
        return false;
      }
      $this->file[$handle] = $this->filename($filename);
    } else {
      reset($handle);
      while(list($h, $f) = each($handle)) {
        $this->file[$h] = $this->filename($f);
      }
    }
  }

  /* public: set_block(string $parent, string $handle, string $name = "")
   * extract the template $handle from $parent, 
   * place variable {$name} instead.
   */
  function set_block($parent, $handle, $name = "") {
    if (!$this->loadfile($parent)) {
      $this->halt("subst: unable to load $parent.");
      return false;
    }
    if ($name == "")
      $name = $handle;

    $str = $this->get_var($parent);
    $reg = "/<!--\s+BEGIN $handle\s+-->(.*)\n\s*<!--\s+END $handle\s+-->/sm";
    preg_match_all($reg, $str, $m);
    $str = preg_replace($reg, "{" . "$name}", $str);
    $this->set_var($handle, $m[1][0]);
    $this->set_var($parent, $str);
  }
  
  /* public: set_var(array $values)
   * values: array of variable name, value pairs.
   *
   * public: set_var(string $varname, string $value)
   * varname: name of a variable that is to be defined
   * value:   value of that variable
   */
  function set_var($varname, $value = "") {
    if (!is_array($varname)) {
      if (!empty($varname))
        if ($this->debug) print "scalar: set *$varname* to *$value*<br>\n";
        $this->varkeys[$varname] = "/".$this->varname($varname)."/";
        $this->varvals[$varname] = $value;
    } else {
      reset($varname);
      while(list($k, $v) = each($varname)) {
        if (!empty($k))
          if ($this->debug) print "array: set *$k* to *$v*<br>\n";
          $this->varkeys[$k] = "/".$this->varname($k)."/";
          $this->varvals[$k] = $v;
      }
    }
  }

  /* public: subst(string $handle)
   * handle: handle of template where variables are to be substituted.
   */
  function subst($handle) {
    if (!$this->loadfile($handle)) {
      $this->halt("subst: unable to load $handle.");
      return false;
    }

    $str = $this->get_var($handle);
    $str = @preg_replace($this->varkeys, $this->varvals, $str);
    return $str;
  }
  
  /* public: psubst(string $handle)
   * handle: handle of template where variables are to be substituted.
   */
  function psubst($handle) {
    print $this->subst($handle);
    
    return false;
  }

  /* public: parse(string $target, string $handle, boolean append)
   * public: parse(string $target, array  $handle, boolean append)
   * target: handle of variable to generate
   * handle: handle of template to substitute
   * append: append to target handle
   */
  function parse($target, $handle, $append = false) {
    if (!is_array($handle)) {
      $str = $this->subst($handle);
      if ($append) {
        $this->set_var($target, $this->get_var($target) . $str);
      } else {
        $this->set_var($target, $str);
      }
    } else {
      reset($handle);
      while(list($i, $h) = each($handle)) {
        $str = $this->subst($h);
        $this->set_var($target, $str);
      }
    }
    
    return $str;
  }
  
  function pparse($target, $handle, $append = false) {
    print $this->parse($target, $handle, $append);
    return false;
  }
  
  /* public: get_vars()
   */
  function get_vars() {
    reset($this->varkeys);
    while(list($k, $v) = each($this->varkeys)) {
      $result[$k] = $this->varvals[$k];
    }
    
    return $result;
  }
  
  /* public: get_var(string varname)
   * varname: name of variable.
   *
   * public: get_var(array varname)
   * varname: array of variable names
   */
  function get_var($varname) {
    if (!is_array($varname)) {
      return $this->varvals[$varname];
    } else {
      reset($varname);
      while(list($k, $v) = each($varname)) {
        $result[$k] = $this->varvals[$k];
      }
      
      return $result;
    }
  }
  
  /* public: get_undefined($handle)
   * handle: handle of a template.
   */
  function get_undefined($handle) {
    if (!$this->loadfile($handle)) {
      $this->halt("get_undefined: unable to load $handle.");
      return false;
    }
    
    preg_match_all("/\{([^}]+)\}/", $this->get_var($handle), $m);
    $m = $m[1];
    if (!is_array($m))
      return false;

    reset($m);
    while(list($k, $v) = each($m)) {
      if (!isset($this->varkeys[$v]))
        $result[$v] = $v;
    }
    
    if (count($result))
      return $result;
    else
      return false;
  }

  /* public: finish(string $str)
   * str: string to finish.
   */
  function finish($str) {
    switch ($this->unknowns) {
      case "keep":
      break;
      
      case "remove":
        $str = preg_replace('/{[^ \t\r\n}]+}/', "", $str);
      break;

      case "comment":
        $str = preg_replace('/{([^ \t\r\n}]+)}/', "<!-- Template $handle: Variable \\1 undefined -->", $str);
      break;
    }
    
    return $str;
  }

  /* public: p(string $varname)
   * varname: name of variable to print.
   */
  function p($varname) {
    print $this->finish($this->get_var($varname));
  }

  function get($varname) {
    return $this->finish($this->get_var($varname));
  }
    
  /***************************************************************************/
  /* private: filename($filename)
   * filename: name to be completed.
   */
  function filename($filename) {
    if (substr($filename, 0, 1) != "/") {
      $filename = $this->root."/".$filename;
    }
    
    if (!file_exists($filename))
      $this->halt("filename: file $filename does not exist.");

    return $filename;
  }
  
  /* private: varname($varname)
   * varname: name of a replacement variable to be protected.
   */
  function varname($varname) {
    return preg_quote("{".$varname."}");
  }

  /* private: loadfile(string $handle)
   * handle:  load file defined by handle, if it is not loaded yet.
   */
  function loadfile($handle) {
    if (isset($this->varkeys[$handle]) and !empty($this->varvals[$handle]))
      return true;

    if (!isset($this->file[$handle])) {
      $this->halt("loadfile: $handle is not a valid handle.");
      return false;
    }
    $filename = $this->file[$handle];

    $str = implode("", @file($filename));
    if (empty($str)) {
      $this->halt("loadfile: While loading $handle, $filename does not exist or is empty.");
      return false;
    }

    $this->set_var($handle, $str);
    
    return true;
  }

  /***************************************************************************/
  /* public: halt(string $msg)
   * msg:    error message to show.
   */
  function halt($msg) {
    $this->last_error = $msg;
    
    if ($this->halt_on_error != "no")
      $this->haltmsg($msg);
    
    if ($this->halt_on_error == "yes")
      die("<b>Halted.</b>");
    
    return false;
  }
  
  /* public, override: haltmsg($msg)
   * msg: error message to show.
   */
  function haltmsg($msg) {
    printf("<b>Template Error:</b> %s<br>\n", $msg);
  }
}
?>
