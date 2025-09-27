function c(r,e="decimal",n="id-ID",t="IDR"){return new Intl.NumberFormat(n,{style:e==="currency"?"currency":"decimal",currency:t,minimumFractionDigits:0}).format(r)}export{c as f};
