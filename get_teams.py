import requests, json, csv
r = requests.get("http://site.api.espn.com/apis/site/v2/sports/football/nfl/teams?limit=32")
teams = json.loads(r.text)['sports'][0]['leagues'][0]['teams']
writer = csv.writer(open('teams.csv', 'w+'))
for t in teams:
	t = t['team']
	data = [t['id'], t['slug'], t['location'], t['displayName'], t['shortDisplayName'], t['abbreviation'], t['color'], t['alternateColor']]
	writer.writerow(data)