<?php
// includes/classes/Promotion.php
class Promotion {
  public $id;
  public $title;
  public $description;
  public $coupon_code;
  public $discount_type;
  public $discount_value;
  public $start_at;
  public $end_at;
  public $active;
  public $created_at;
  public $updated_at;

  public static function fromRow(array $r): Promotion {
    $p = new self();
    foreach($r as $k=>$v){
      if(property_exists($p, $k)) $p->$k = $v;
    }
    return $p;
  }
}
