function greet(lang1){
    console.log(`hello ${this.name} i know ${lang1}`);
    
}

const user = {
    name: "zxn",
    age: 27
}

lang = ["gfdfsdsf"]

greet.call(user, lang[0])
greet.apply(user, lang)
var f = greet.bind(user, lang[0])
f()

var name = "gdfgdfg"
greet("99999")