// Simple interactivity
document.querySelectorAll('.hero .dots span').forEach((d,i,arr)=>{
  d.addEventListener('click',()=>{arr.forEach(x=>x.classList.remove('active'));d.classList.add('active')});
});
document.querySelectorAll('.carousel-ctrl button').forEach(b=>{
  b.addEventListener('click',()=>b.animate([{transform:'scale(1)'},{transform:'scale(.9)'},{transform:'scale(1)'}],{duration:200}));
});
const nForm=document.querySelector('.newsletter');
if(nForm){nForm.addEventListener('submit',e=>{e.preventDefault();const i=nForm.querySelector('input');alert('Subscribed: '+i.value);i.value=''});}
