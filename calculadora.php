<?php
session_start();
if (!isset($_SESSION['history'])) $_SESSION['history'] = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['expression'], $_POST['result'])) {
    $expr = trim($_POST['expression']);
    $res  = trim($_POST['result']);
    if ($expr !== '' && $res !== '') {
        $_SESSION['history'][] = ['expr' => $expr, 'res' => $res];
        if (count($_SESSION['history']) > 10) array_shift($_SESSION['history']);
    }
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<title>Calculadora Científica</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
body {
  font-family: 'Segoe UI', Arial, sans-serif;
  background: #0d1117; /* fundo preto elegante */
  color: #f0f6fc;
  margin: 0;
  height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
}
.calc {
  background: #161b22;
  padding: 25px;
  border-radius: 16px;
  box-shadow: 0 0 25px rgba(0, 0, 0, 0.6);
  max-width: 400px;
  width: 100%;
}
.display {
  width: 100%;
  height: 60px;
  font-size: 22px;
  padding: 8px;
  margin-bottom: 12px;
  text-align: right;
  box-sizing: border-box;
  border: none;
  border-radius: 8px;
  background: #0d1117;
  color: #f0f6fc;
}
.display:focus {
  outline: 2px solid #58a6ff;
}
.buttons {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 8px;
}
button {
  height: 48px;
  font-size: 17px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: 0.15s;
  color: #f0f6fc;
}
button:hover {
  filter: brightness(1.2);
}
.num { background: #30363d; }
.op  { background: #238636; }
.fun { background: #6e40c9; }
.eq  { background: #1f6feb; grid-column: span 2; }
.clr { background: #da3633; }

.hist {
  margin-top: 15px;
  font-size: 14px;
  background: #21262d;
  border-radius: 8px;
  padding: 10px;
  max-height: 180px;
  overflow-y: auto;
}
.hist div {
  margin: 4px 0;
  border-bottom: 1px solid #30363d;
  padding-bottom: 4px;
}
.hist strong { color: #58a6ff; }
::-webkit-scrollbar { width: 6px; }
::-webkit-scrollbar-thumb { background: #30363d; border-radius: 10px; }
</style>
</head>
<body>
<div class="calc">
  <form id="calcForm" method="post">
    <input class="display" id="display" name="expression" readonly>
    <input type="hidden" name="result" id="resultInput">

    <div class="buttons">
      <button type="button" class="fun" onclick="press('sin(')">sin</button>
      <button type="button" class="fun" onclick="press('cos(')">cos</button>
      <button type="button" class="fun" onclick="press('tan(')">tan</button>
      <button type="button" class="fun" onclick="press('log(')">log</button>
      <button type="button" class="fun" onclick="press('√(')">√</button>

      <button type="button" class="num" onclick="press('7')">7</button>
      <button type="button" class="num" onclick="press('8')">8</button>
      <button type="button" class="num" onclick="press('9')">9</button>
      <button type="button" class="op" onclick="press('/')">÷</button>
      <button type="button" class="fun" onclick="press('^')">xʸ</button>

      <button type="button" class="num" onclick="press('4')">4</button>
      <button type="button" class="num" onclick="press('5')">5</button>
      <button type="button" class="num" onclick="press('6')">6</button>
      <button type="button" class="op" onclick="press('*')">×</button>
      <button type="button" class="fun" onclick="press('!')">n!</button>

      <button type="button" class="num" onclick="press('1')">1</button>
      <button type="button" class="num" onclick="press('2')">2</button>
      <button type="button" class="num" onclick="press('3')">3</button>
      <button type="button" class="op" onclick="press('-')">−</button>
      <button type="button" class="fun" onclick="press('π')">π</button>

      <button type="button" class="num" onclick="press('0')">0</button>
      <button type="button" class="num" onclick="press('.')">.</button>
      <button type="button" class="op" onclick="press('+')">+</button>
      <button type="button" class="fun" onclick="press('(')">(</button>
      <button type="button" class="fun" onclick="press(')')">)</button>

      <button type="button" class="clr" onclick="clr()">C</button>
      <button type="button" class="fun" onclick="press('%')">%</button>
      <button type="submit" class="eq">=</button>
    </div>
  </form>

  <?php if (!empty($_SESSION['history'])): ?>
  <div class="hist">
    <strong>Histórico</strong>
    <?php foreach (array_reverse($_SESSION['history']) as $h): ?>
      <div><?php echo htmlentities($h['expr']); ?> = <strong><?php echo htmlentities($h['res']); ?></strong></div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

<script>
const display = document.getElementById('display');
const resultInput = document.getElementById('resultInput');

function press(ch) {
  display.value += ch;
}
function clr() {
  display.value = '';
}

document.getElementById('calcForm').addEventListener('submit', function(e){
  e.preventDefault();
  let expr = display.value;
  try {
    let res = evaluateExpression(expr);
    resultInput.value = res;
    this.submit();
  } catch {
    alert('Expressão inválida');
  }
});

function evaluateExpression(expr) {
  expr = expr.replace(/π/g, Math.PI);
  expr = expr.replace(/e/g, Math.E);
  expr = expr.replace(/√\(/g, 'Math.sqrt(');
  expr = expr.replace(/sin\(/g, 'Math.sin(');
  expr = expr.replace(/cos\(/g, 'Math.cos(');
  expr = expr.replace(/tan\(/g, 'Math.tan(');
  expr = expr.replace(/log\(/g, 'Math.log10(');
  expr = expr.replace(/\^/g, '**');
  expr = expr.replace(/([0-9]+)!/g, 'factorial($1)');
  expr = expr.replace(/%/g, '/100');

  function factorial(n){if(n<0)return NaN;let f=1;for(let i=1;i<=n;i++)f*=i;return f;}
  return Function('factorial', 'return ' + expr)(factorial);
}
</script>
</body>
</html>
