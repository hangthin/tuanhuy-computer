import sys, io, re
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8')

path = "C:/AppServ/www/tuanhuy_computer/app/Views/pages/showcase.php"
with open(path, 'r', encoding='utf-8') as f:
    content = f.read()

def rep(name, old, new):
    global content
    if old in content:
        content = content.replace(old, new, 1)
        print(f'OK: {name}')
    else:
        print(f'FAIL: {name}')

# 1. Remove flex centering from #sc-stage (not needed with absolute pc-group)
rep('#sc-stage flex',
    '  display:flex;align-items:center;justify-content:center;\n}',
    '}',
)

# 2. Replace the entire assembled desk group CSS block
rep('#sc-group / #pc-group CSS',
    """#sc-group{
  position:relative;
  max-width:90vw;
  z-index:10;opacity:0;
}
#pc-group{
  display:grid;
  grid-template-columns:auto auto;
  grid-template-rows:auto auto;
  column-gap:clamp(12px,1.6vw,28px);
  row-gap:clamp(4px,.5vh,8px);
  width:fit-content;
  position:relative;
  margin:auto;
}
.acomp{overflow:visible}
.acomp img{
  width:100%;height:auto;
  object-fit:contain;display:block;pointer-events:none;
}
/* Desk grid placement */
#ac-monitor {
  grid-column:1;grid-row:1;
  width:min(44vw,480px);
  align-self:end;
}
#ac-case {
  grid-column:2;grid-row:1;
  width:min(18vw,200px);
  align-self:center;
  filter:drop-shadow(0 0 22px rgba(229,57,53,.42)) drop-shadow(0 0 8px rgba(229,57,53,.25));
}
#ac-keyboard {
  grid-column:1;grid-row:2;
  width:min(36vw,420px);
  align-self:start;
}
#ac-mouse {
  grid-column:2;grid-row:2;
  width:min(7vw,90px);
  align-self:start;
}
/* Internal (inside case) \u2013 visible only when spotlighted */
#ac-cpu,#ac-ram,#ac-gpu,#ac-cooler,#ac-ssd{
  position:absolute;
  width:17%;height:24%;right:6%;top:22%;opacity:0;
}""",
    """#sc-group{
  position:absolute;inset:0;
  z-index:10;opacity:0;
}
#pc-group{
  position:absolute;
  top:50%;left:50%;
  transform:translate(-50%,-50%) scale(var(--scale,1));
  transform-origin:center center;
  width:900px;height:600px;
}
.acomp{overflow:visible}
.acomp img{width:100%;height:auto;display:block;pointer-events:none}
/* Fixed pixel positions inside 900\u00d7600 canvas */
#ac-monitor  {position:absolute;left:80px; top:40px; width:520px}
#ac-case     {position:absolute;left:630px;top:20px; width:240px;
              filter:drop-shadow(0 0 22px rgba(229,57,53,.42)) drop-shadow(0 0 8px rgba(229,57,53,.25))}
#ac-keyboard {position:absolute;left:120px;top:460px;width:450px}
#ac-mouse    {position:absolute;left:600px;top:450px;width:110px}
/* Internal (inside case) \u2013 visible only when spotlighted */
#ac-cpu,#ac-ram,#ac-gpu,#ac-cooler,#ac-ssd{
  position:absolute;
  width:17%;height:24%;right:6%;top:22%;opacity:0;
}""",
)

# 3. Add pcGroup DOM ref + setScale after scReassemble
rep('pcGroup DOM + setScale',
    "const scReassemble = document.getElementById('sc-reassemble');",
    """const scReassemble = document.getElementById('sc-reassemble');
const pcGroup      = document.getElementById('pc-group');

// ── Scale #pc-group to fit viewport ───────────────────────────
function setScale(){
  const s = Math.min(window.innerWidth * 0.85 / 900, window.innerHeight * 0.8 / 600);
  pcGroup.style.setProperty('--scale', s);
}
setScale();
window.addEventListener('resize', setScale);""",
)

with open(path, 'w', encoding='utf-8') as f:
    f.write(content)
print('WRITTEN')
