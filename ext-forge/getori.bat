@for %i in (@patch.txt) (copy app\development\%i patch\ori\%i & copy app\development\%i patch\new\%i)
