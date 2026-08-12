const fs = require('fs');
let c1 = fs.readFileSync('C:/XAMPP/htdocs/Ascendance/wp-content/themes/ascendance/page-cami-registry.php', 'utf8');
c1 = c1.replace('    });\n    document.getElementById(\'pager\').innerHTML=buildPager(total);', '    }).join(\'\');\n    document.getElementById(\'pager\').innerHTML=buildPager(total);');
c1 = c1.replace('top:0;z-index:10;', 'top:140px;z-index:10;');
c1 = c1.replace('top:62px;z-index:5;', 'top:205px;z-index:5;');
fs.writeFileSync('C:/XAMPP/htdocs/Ascendance/wp-content/themes/ascendance/page-cami-registry.php', c1);

let c2 = fs.readFileSync('C:/XAMPP/htdocs/Ascendance/wp-content/themes/ascendance/page-sar-registry.php', 'utf8');
c2 = c2.replace('      </div>\n      ;\n    });', '      </div>\n      ;\n    }).join(\'\');');
c2 = c2.replace('top:0;z-index:10;', 'top:140px;z-index:10;');
c2 = c2.replace('top:62px;z-index:5;', 'top:205px;z-index:5;');
fs.writeFileSync('C:/XAMPP/htdocs/Ascendance/wp-content/themes/ascendance/page-sar-registry.php', c2);
console.log('Fixed');

