from locust import HttpLocust, TaskSet
from bs4 import BeautifulSoup

def login(self):
    response = self.client.get("/user/login", name="Login")
    soup = BeautifulSoup(response.text, "html.parser")
    drupal_form_id = soup.select('input[name="form_build_id"]')[0]["value"]
    r = self.client.post("/", {"name":"admin", "pass":"drupal8", "form_id":"user_login_form", "op":"Log+in", "form_build_id":drupal_form_id}, name='Login')

def logout(self):
    self.client.get("/user/logout", name='Log out')

def admin(self):
    self.client.get("/admin", name="Admin")

class UserBehavior(TaskSet):
    tasks = {admin: 1}

    def on_start(self):
        login(self)

    def on_stop(self):
        logout(self)

class WebsiteUser(HttpLocust):
    task_set = UserBehavior
    min_wait = 2000
    max_wait = 10000

