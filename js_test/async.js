
function returnd (num) {
    return new Promise((res,rej) => {
      setTimeout(() => {
        res(2 * num)
      },2000)
    })
  }
  
  async function testre () {
    let aa = await returnd(10)
    console.log(aa);
    let bb = await returnd(20)
    console.log(bb);
    let cc = await returnd(30)
    console.log(cc);
    console.log(aa + bb + cc);
  
  
    const [dd, ee, ff] = await Promise.all([returnd(10), returnd(20), returnd(30)])
  
    return (dd+ee+ff)
  }
  
  testre().then(res => {
    console.log(res);
    
  })