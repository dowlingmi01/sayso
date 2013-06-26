@for %i in (@unpack.txt) (python c:\pgm\einars-js-beautify\python\js-beautify app\development\%i.js > patch\unpack\%i.js & copy patch\unpack\%i.js patch\new\%i.js)
