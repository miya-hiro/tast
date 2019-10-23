<?php

ini_set('log_errors','on');
ini_set('error_log','php.log');
session_start();

$debug_flg = true;
//デバッグログ関数
function debug($str){
  global $debug_flg;
  if(!empty($debug_flg)){
    error_log('デバッグ：'.$str);
  }
}
// debugLogStart();

// 自分の初期値(定数)
define("MY_LV", 1);
define("MY_WP", 0);
define("MY_EP", 0);
define("MY_HP", 0);
define("MY_IMG", 'img/kinoko.png' );

// アイテム格納用
$items = array();
$words = array();

// クラス（設計図）の作成(１文字目を大文字で)
class Item{
  // プロパティ protectedならサブクラスでも呼び出せる
   protected $name;  // 定義しただけだとnullが入る
   protected $lv;
   protected $w_p;
   protected $e_p;
   protected $h_p;
   protected $history;

  // コンストラクタ
   public function __construct($name, $lv, $w_p, $e_p, $h_p, $history){
       $this->name = $name;
       $this->lv = $lv;
       $this->w_p = $w_p;
       $this->e_p = $e_p;
       $this->h_p = $h_p;
       $this->history = $history;
   }

  // メソッド
   public function give(){
    $_SESSION['history'] = $this->history.'<br>';
    $_SESSION['mylv'] += $this->lv;
    $_SESSION['mywp'] += $this->w_p ;
    $_SESSION['myep'] += $this->e_p ;
    $_SESSION['myhp'] += $this->h_p;
    sayWord();
   }
  // セッター
    public function setlv($num){
    $this->lv = filter_var($num, FILTER_VALIDATE_INT);
    }
    public function setw_p($num){
    // セッターを使うことで、直接代入させずにバリデーションチェックを行ってから代入させることができる
    $this->w_p = filter_var($num, FILTER_VALIDATE_INT);
    }
    public function sete_p($num){
    // $numには小数点が入る可能性がある。filter_var関数はバリデーションにひっかかるとfalseが返ってきて
    //代入されてしまうので、float型かどうかのバリデーションにして、int型へキャスト
    // もしくは、FILTER_VALIDATE_FLOATを使う
    $this->e_p = (int)filter_var($num, FILTER_VALIDATE_FLOAT);
    }  
    public function seth_p($num){
    // セッターを使うことで、直接代入させずにバリデーションチェックを行ってから代入させることができる
    $this->h_p = filter_var($num, FILTER_VALIDATE_INT);
    }
 
    // ゲッター
    public function getlv(){
     return $this->lv;
    }
    public function getw_p(){
     return $this->w_p;
    }
    public function gete_p(){
     return $this->e_p;
    }
    public function geth_p(){
     return $this->h_p;
    }
    }

// ネガティブな影響を与えるアイテムクラス
class NegaItem extends Item{
    function __construct($name, $lv, $w_p, $e_p, $h_p, $history){
// 親クラスのコンストラクタで処理する内容を継承したい場合には親コンストラクタを呼び出す。
    parent::__construct($name, $lv, $w_p, $e_p, $h_p, $history);
  }
    public function give(){
        $_SESSION['history'] = $this->history.'<br>';
        $_SESSION['mylv'] += $this->lv;
        $_SESSION['mywp'] += $this->w_p ;
        $_SESSION['myep'] += $this->e_p ;
        $_SESSION['myhp'] += $this->h_p;
        $_SESSION['history'] .= 'きのこ「 ・・・ 」<br>';
    }
}

//キノコの(良い)セリフクラス
class Word{
    protected $word;
  public function __construct($word){
       $this->word = $word;
  }
  public function getWord(){
    return $this->word;
  }
}

// クラスを造ったらインスタンス生成
$items[] = new Item('お水', 1, 10, 0, 0 ,'お水をあげた');
$items[] = new Item('肥料', 1, 0, 10, 0 ,'ひりょうをあげた');
$items[] = new Item('言葉', 0.5, 0, 0, 10, 'ことばをかけた');
$items[] = new NegaItem('no', -1, 0, 0, 0,'何もしなかった');

$words[] = new Word('　❤❤　');
$words[] = new Word('　 ❤ 　');
$words[] = new Word('ありがと！');
$words[] = new Word('うれしいな');


function createItem(){
    global $items;
    if(!empty($_POST['water'])){
        $_SESSION['item'] = $items[0];
    } else if (!empty($_POST['energy'])){
        $_SESSION['item'] = $items[1];
    } else if (!empty($_POST['kotoba'])){
        $_SESSION['item'] = $items[2];
    } else if (!empty($_POST['no'])){
        $_SESSION['item'] = $items[3];
    }
}

function sayWord(){
    global $words;
    $word = $words[mt_rand(0,3)];
    $_SESSION['history'] .= 'きのこ 「 '.$word->getWord().' 」<br>';
}

function init(){
    $_SESSION['history'] .= '大事に育てましょう！<br>';
    $_SESSION['mylv'] = MY_LV;
    $_SESSION['mywp'] = MY_WP;
    $_SESSION['myep'] = MY_EP;
    $_SESSION['myhp'] = MY_HP;
    $_SESSION['myimg'] = MY_IMG;
    $_SESSION['w_full'] = 'false';
    $_SESSION['goal'] = 'false';
}
function gameOver(){
    $_SESSION = array();
}

//1.post送信されていた場合
if(!empty($_POST)){
    $startFlg = (!empty($_POST['start'])) ? true : false;
    $waterFlg = (!empty($_POST['water'])) ? true : false;
    $energyFlg = (!empty($_POST['energy'])) ? true : false;
    $kotobaFlg = (!empty($_POST['kotoba'])) ? true : false;
    $noFlg = (!empty($_POST['no'])) ? true : false;
    error_log('POSTされた！');
    debug('ポストの中身：'.print_r($_POST,true));

   if($startFlg){
        $_SESSION['history'] = 'ゲームスタート！<br>';
        init();
    } else {
        createItem();
        $_SESSION['item']->give(); 

        if($waterFlg){
            // 水をあげすぎた場合
          if($_SESSION['mylv'] === 5 && $_SESSION['mywp'] >= 40){
                $_SESSION['w_full'] = 'true';
                $_SESSION['myimg'] = 'img/karekinoko.png';
                $_SESSION['history'] = '水をあげすぎて腐ってしまった！ <br>';  
                // gameOver();
            }
          }       
        if($energyFlg) {
            // ひりょうをあげすぎた場合
            if($_SESSION['mylv'] === 5 && $_SESSION['myep'] >= 40){
                $_SESSION['w_full'] = 'true';
                $_SESSION['myimg'] = 'img/hutokinoko.png';
                $_SESSION['history'] = '太りすぎて腐ってしまった！ <br>';  
            }
         }
        
         if($kotobaFlg) {
            //ことばをかけすぎた場合 
            if($_SESSION['myhp'] === 100){
               $_SESSION['w_full'] = 'true';
               $_SESSION['myimg'] = 'img/karekinoko.png';
               $_SESSION['history'] = 'つかれて枯れてしまった！ <br>';  
           }
         }
 
        if($_SESSION['mylv'] <= 0){
            $_SESSION['w_full'] = 'true';
            $_SESSION['myimg'] = 'img/karekinoko.png';
            $_SESSION['history'] = '干からびてしまった！ <br>';  
        }
      }
     // lvに合わせて成長させる
     //($_SESSION['w_full'] = 'false')とすると代入になってしまう！！
     if($_SESSION['w_full'] === 'false'){ 
          if($_SESSION['mylv'] >= 3.5 && $_SESSION['mylv'] < 5.5){
            $_SESSION['myimg'] =  'img/kinokolv4.png'; 
          } else if($_SESSION['mylv'] >= 5.5 && $_SESSION['mylv'] < 8){
            $_SESSION['myimg'] =  'img/kinokolv7.png'; 
          } else if($_SESSION['mylv'] >= 8 && $_SESSION['mylv'] < 10){
            $_SESSION['myimg'] =  'img/kinokolv8.png'; 
          }
      }

     // lvが１０以上になったらクリア
        if($_SESSION['mylv'] >= 10){
            if($_SESSION['mywp'] <= $_SESSION['myhp'] && $_SESSION['myep'] <= $_SESSION['myhp']){
               $_SESSION['history'] = 'おめでとうございます！<br>きのこの妖精に育ちました！';
               $_SESSION['myimg'] =  'img/yousei.png';
               $_SESSION['goal'] = 'true';
            } else if($_SESSION['myhp'] <= $_SESSION['myep'] && $_SESSION['mywp'] <= $_SESSION['myep']){
               $_SESSION['history'] = 'おめでとうございます！<br>太ったきのこに育ちました！';
               $_SESSION['myimg'] =  'img/huto.png';
               $_SESSION['goal'] = 'true';
            } else {
               $_SESSION['history'] = 'おめでとうございます！<br>美味しそうなきのこに育ちました！';
               $_SESSION['myimg'] =  'img/normal.png';
               $_SESSION['goal'] = 'true';
            }
        }
        $_POST = array();
}

// var_dump($_SESSION['mylv']);
// var_dump($_SESSION['mywp']);
// var_dump($_SESSION['myep']);
// var_dump($_SESSION['myhp']);
// var_dump($_SESSION['myimg']);
// var_dump($_SESSION['w_full']);
?>

<!DOCTYPE html>
<html>
    <head>
    <meta charset="utf-8">
    <title>ゲーム！きのこちゃん</title>
    <link href="https://use.fontawesome.com/releases/v5.6.1/css/all.css" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <link rel="stylesheet" type="text/css" href="style.css">
    <link href="https://fonts.googleapis.com/css?family=Sawarabi+Gothic&display=swap" rel="stylesheet">   
    </head>
    <body>
    <div class="wrap">    
    <h1 style="text-align:center;">ゲーム！きのこちゃん</h1>
      <?php if(empty($_SESSION)){ ?>
     <div class="top site-width">
       <div class="kaisetu"> 
        <p><i class="fas fa-caret-right"></i>きのこちゃんのお世話をして、育ててあげましょう！</p>    
        <p><i class="fas fa-caret-right"></i>きのこちゃんの成長エンドは３パターン！<br>育て方をまちがえると枯れてしまうから気をつけて！</p>
       </div> 
          <form method="post">
          <input type="submit" name="start" value="スタート！">
          </form> 
     </div>  
<!-- レベル５の失敗ケース            -->
        <?php }else if( ($_SESSION['w_full']) === 'true'){ ?>
    <div class="site-width main-wrap"> 
        <div class="side-bar">
          <form method="post">
             <input type="submit" name="start" value="やりなおす"> 
          </form>  
        </div>
        <div class="main">
         <img src="<?php echo (!empty($_SESSION['myimg'])) ? $_SESSION['myimg'] : 'img/kinoko.png'; ?>" class="buruburu">
        </div>
      </div>
      <div class="history site-width">
        <p><?php echo (!empty($_SESSION['history'])) ? $_SESSION['history'] : ''; ?></p>
      </div>
<!-- ゴールした場合 -->
      <?php }else if(($_SESSION['goal']) === 'true'){ ?>
        <div class="site-width main-wrap"> 
        <div class="side-bar">
          <form method="post">
             <input type="submit" name="start" value="もう一度する"> 
          </form>  
        </div>
        <div class="main">
         <img src="<?php echo (!empty($_SESSION['myimg'])) ? $_SESSION['myimg'] : 'img/kinoko.png'; ?>" class="huwahuwa">
        </div>
      </div>
      <div class="history site-width">
        <p><?php echo (!empty($_SESSION['history'])) ? $_SESSION['history'] : ''; ?></p>
      </div>

        <?php }else{ ?>
      <div class="site-width main-wrap"> 
        <div class="side-bar">
          <form class="go-btn" method="post">
            <div class="btn-container">  
             <input type="submit" name="water" value="お水"> 
             <input type="submit" name="energy" value="ひりょう"> 
             <input type="submit" name="kotoba" value="ことば"> 
             <input type="submit" name="no" value="何もしない"> 
             <input type="submit" name="start" value="リセット" class="reset-btn"> 
            </div>
         </form>  
        </div>
        <div class="main">
         <img src="<?php echo (!empty($_SESSION['myimg'])) ? $_SESSION['myimg'] : 'img/kinoko.png'; ?>" class="huwahuwa">
        </div>
      </div>
      
      <div class="history site-width">
        <p><?php echo (!empty($_SESSION['history'])) ? $_SESSION['history'] : ''; ?></p>
      </div>
      <?php } ?>
      </div>  
    </body>
</html>

