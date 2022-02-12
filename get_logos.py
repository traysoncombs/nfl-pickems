import requests, json
teams = json.loads(requests.get("http://site.api.espn.com/apis/site/v2/sports/football/nfl/teams?limit=32").text)['sports'][0]['leagues'][0]['teams']
for t in teams:
	t = t['team']
	abbr = t['abbreviation']
	img_url = t['logos'][0]['href']
	img_data = requests.get(img_url).content
	with open('logos/' + abbr+'.png', 'wb+') as f:
		f.write(img_data)