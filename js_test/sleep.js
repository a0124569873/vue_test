var sleep = async (tt) => {
    return new Promise((res,rej) => {
        setTimeout(res, tt)
    })
}



(async () => {
    console.log("start sleep");
    // await sleep(2000)
    // await sleep(2000)
    await Promise.all([sleep(1000), sleep(2000)])
    console.log("end sleep");
})()


