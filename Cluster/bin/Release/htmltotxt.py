from bs4 import BeautifulSoup
html = open('./web/4.html', 'r')
soup = BeautifulSoup(html.read())
html.close()
anchors = soup.findAll('p')
print(anchors)
