import random

file = open("bar.JSON", "w")

start = 1514764800
day = 86400
end = 1527465600

file.write('{')
file.write('"s" : "ok", \n')
file.write('"t" : ')
file.write('[' + str(start))
cur = start
cnt = 1
while True:
	cur += day
	cnt += 1
	if cnt >= 6:
		cnt = 1
		cur += day * 2
	if cur >= end:
		break
	file.write("," + str(cur))
file.write('] , \n')

file.write('"c" : ')
file.write('[ 42')
cur = start
cnt = 1
while True:
	cur += day
	cnt += 1
	if cnt >= 5:
		cnt = 1
		cur += day * 2
	if cur > end:
		break
	file.write("," + str(random.randint(40,90)))
file.write('] , \n')

file.write('"o" : ')
file.write('[ 50')
cur = start
cnt = 1
while True:
	cur += day
	cnt += 1
	if cnt >= 5:
		cnt = 1
		cur += day * 2
	if cur >= end:
		break
	file.write("," + str(random.randint(40,90)))
file.write('] , \n')

file.write('"h" : ')
file.write('[ 50')
cur = start
cnt = 1
while True:
	cur += day
	cnt += 1
	if cnt >= 5:
		cnt = 1
		cur += day * 2
	if cur >= end:
		break
	file.write("," + str(random.randint(50,110)))
file.write('] , \n')

file.write('"l" : ')
file.write('[ 50')
cur = start
cnt = 1
while True:
	cur += day
	cnt += 1
	if cnt >= 5:
		cnt = 1
		cur += day * 2
	if cur >= end:
		break
	file.write("," + str(random.randint(20,70)))
file.write('] , \n')

file.write('"v" : ')
file.write('[ 3000')
cur = start
cnt = 1
while True:
	cur += day
	cnt += 1
	if cnt >= 5:
		cnt = 1
		cur += day * 2
	if cur >= end:
		break
	file.write("," + str(random.randint(1000,6000)))
file.write('] \n')

file.write('}')