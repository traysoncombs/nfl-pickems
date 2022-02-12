import requests, json, csv, pytz, datetime
r  = requests.get("http://site.api.espn.com/apis/site/v2/sports/football/nfl/scoreboard?dates=20190905-20191220&limit=600") # gets all games between date range
r_json = json.loads(r.text)
print(len(r_json['events']))
writer = csv.writer(open('old_events.csv', 'w+'))
start_week = datetime.datetime.strptime("2019-09-06T00:20Z", "%Y-%m-%dT%H:%MZ") # all this stuff is used to calculate what week an event is taking place on
start_week = int(pytz.timezone("GMT").localize(start_week).timestamp()) # this is the starting week in unix timw
for e in r_json['events']: # loops through all events
	team_one = e['competitions'][0]['competitors'][0]
	team_two = e['competitions'][0]['competitors'][1]
	t = datetime.datetime.strptime(e['date'], "%Y-%m-%dT%H:%MZ")
	t = pytz.timezone("GMT").localize(t).timestamp() # unix time of current event
	week = int((t - start_week) // (60 * 60 * 24 * 7)) + 1 # calculates the week, starts by subtracting current event from starting week which gives how many seconds it has been since first week, we then simply divide it by how many seconds are in a week
	data = [int(e['id']), int(t), e['name'], e['shortName'], e['competitions'][0]['status']['type']['completed'], int(team_one['id']), 0, int(team_two['id']), 0, "NULL", week] # cancer
	print(data)
	writer.writerow(data)