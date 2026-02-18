const urlBase = 'http://portcall.cloud/LAMPAPI';
const extension = 'php';

let userId = 0;
let firstName = "";
let lastName = "";

function doLogin() {
	userId = 0;
	firstName = "";
	lastName = "";

	let login = document.getElementById("loginName").value;
	let password = document.getElementById("loginPassword").value;
	//	var hash = md5( password );

	document.getElementById("loginResult").innerHTML = "";

	let tmp = { login: login, password: password };
	//	var tmp = {login:login,password:hash};
	let jsonPayload = JSON.stringify(tmp);

	let url = urlBase + '/Login.' + extension;

	let xhr = new XMLHttpRequest();
	xhr.open("POST", url, true);
	xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");
	try {
		xhr.onreadystatechange = function () {
			if (this.readyState == 4 && this.status == 200) {
				let jsonObject = JSON.parse(xhr.responseText);
				userId = jsonObject.id;

				if (userId < 1) {
					document.getElementById("loginResult").innerHTML = "User/Password combination incorrect";
					return;
				}

				firstName = jsonObject.firstName;
				lastName = jsonObject.lastName;

				saveCookie();

				window.location.href = "contacts.html";
			}
		};
		xhr.send(jsonPayload);
	}
	catch (err) {
		document.getElementById("loginResult").innerHTML = err.message;
	}

}

document.addEventListener("DOMContentLoaded", function() {
    const loginBtn = document.querySelectorAll(".toggle-btn")[0];
    const signupBtn = document.querySelectorAll(".toggle-btn")[1];
    const slider = document.getElementById("btn");
    const loginForm = document.getElementById("login");
    const signupForm = document.getElementById("signup");

    loginBtn.addEventListener("click", function() {
        loginForm.style.left = "0px";      // show login
        signupForm.style.left = loginForm.offsetWidth + "px"; // hide signup to right
        slider.style.left = "0px";         // move slider under login
    });

    signupBtn.addEventListener("click", function() {
        loginForm.style.left = -loginForm.offsetWidth + "px"; // hide login to left
        signupForm.style.left = "0px";  // show signup
        slider.style.left = slider.offsetWidth + "px"; // move slider under signup
    });
});

function doSignup() 
{
    let firstName = document.getElementById("signupFirstName").value;
    let lastName = document.getElementById("signupLastName").value;
    let login = document.getElementById("signupLogin").value;
    let password = document.getElementById("signupPassword").value;

    document.getElementById("signupResult").innerHTML = "";

    let tmp = {firstName:firstName, lastName:lastName, login:login, password:password};
    let jsonPayload = JSON.stringify(tmp);

    let url = urlBase + '/SignUp.' + extension;

    let xhr = new XMLHttpRequest();
    xhr.open("POST", url, true);
    xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");

    try
    {
        xhr.onreadystatechange = function()
        {
            if(this.readyState == 4 && this.status == 200)
            {
                let jsonObject = JSON.parse(xhr.responseText);

                if(jsonObject.error && jsonObject.error !== "")
                {
                    document.getElementById("signupResult").innerHTML = jsonObject.error;
                }
                else
                {
                    // Sign-up successful
                    userId = jsonObject.userId;
                    firstName = jsonObject.firstName;
                    lastName = jsonObject.lastName;

                    saveCookie();
                    document.getElementById("signupResult").style.color = "green";
                    document.getElementById("signupResult").innerHTML = "Sign-up successful! Redirecting...";

                    setTimeout(() => { window.location.href = "contacts.html"; }, 1000);
                }
            }
        };
        xhr.send(jsonPayload);
    }
    catch(err)
    {
        document.getElementById("signupResult").innerHTML = err.message;
    }
}

function saveCookie() {
	let minutes = 20;
	let date = new Date();
	date.setTime(date.getTime() + (minutes * 60 * 1000));
	document.cookie = "firstName=" + firstName + ",lastName=" + lastName + ",userId=" + userId + ";expires=" + date.toGMTString();
}

function readCookie() {
	userId = -1;
	let data = document.cookie;
	let splits = data.split(",");
	for (var i = 0; i < splits.length; i++) {
		let thisOne = splits[i].trim();
		let tokens = thisOne.split("=");
		if (tokens[0] == "firstName") {
			firstName = tokens[1];
		}
		else if (tokens[0] == "lastName") {
			lastName = tokens[1];
		}
		else if (tokens[0] == "userId") {
			userId = parseInt(tokens[1].trim());
		}
	}

	if (userId < 0) {
		window.location.href = "index.html";
	}
	else {
		document.getElementById("userName").innerHTML = "Logged in as " + firstName + " " + lastName;
		loadContacts();
	}
}

function doLogout() {
	userId = 0;
	firstName = "";
	lastName = "";
	document.cookie = "firstName= ; expires = Thu, 01 Jan 1970 00:00:00 GMT";
	window.location.href = "index.html";
}

function addContact() {
	readCookie();

	let firstName = document.getElementById("firstName").value;
	let lastName = document.getElementById("lastName").value;
	let email = document.getElementById("email").value;
	let phone = document.getElementById("phone").value;
	document.getElementById("contactAddResult").innerHTML = "";

	let tmp = { firstName: firstName, lastName: lastName, email: email, phone: phone, userId: userId };
	let jsonPayload = JSON.stringify(tmp);

	let url = urlBase + '/AddContact.' + extension;

	let xhr = new XMLHttpRequest();
	xhr.open("POST", url, true);
	xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");
	try {
		xhr.onreadystatechange = function () {
			if (this.readyState == 4 && this.status == 200) {
				let jsonObject = JSON.parse(xhr.responseText);
				if (jsonObject.error && jsonObject.error !== "") {
					document.getElementById("contactAddResult").innerHTML = jsonObject.error;
				}
				else {
					document.getElementById("contactAddResult").innerHTML = "Contact has been added";
					// Clear the form fields
					document.getElementById("firstName").value = "";
					document.getElementById("lastName").value = "";
					document.getElementById("email").value = "";
					document.getElementById("phone").value = "";
					// Refresh the contacts table
					loadContacts();
				}
			}
		};
		xhr.send(jsonPayload);
	}
	catch (err) {
		document.getElementById("contactAddResult").innerHTML = err.message;
	}

}

function toggleAddContactForm() {
    const form = document.getElementById("addContactForm");

    if (form.style.display === "none" || form.style.display === "") {
        // Show the form
        form.style.display = "block";
    } else {
        // Hide the form and reset it back to "add" mode
        form.style.display = "none";
        resetContactForm();
    }
}

function deleteContact(contactId) {
    console.log("deleteContact called with contactId:", contactId);  // DEBUG
    console.log("userId from cookie:", userId);  // DEBUG
    // get userId from cookie 

    readCookie();

    // Confirm deletion
    if (!confirm("Are you sure you want to delete this contact?")) {
        return;
    }

    document.getElementById("contactDeleteResult").innerHTML = "";

    let tmp = { contactId: contactId, userId: userId }; 
    let jsonPayload = JSON.stringify(tmp);

    let url = urlBase + '/DeleteContact.' + extension;

    let xhr = new XMLHttpRequest();
    xhr.open("POST", url, true);
    xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");
    try {
        xhr.onreadystatechange = function () {
            if (this.readyState == 4 && this.status == 200) {
                let jsonObject = JSON.parse(xhr.responseText);
                if (jsonObject.error && jsonObject.error !== "") {
                    document.getElementById("contactDeleteResult").innerHTML = jsonObject.error;
                }
                else {
                    document.getElementById("contactDeleteResult").innerHTML = "Contact has been deleted";
                    // Refresh the contacts table
                    loadContacts();
                }
            }
        };
        xhr.send(jsonPayload);
    }
    catch (err) {
        document.getElementById("contactDeleteResult").innerHTML = err.message;
    }
}

function editContact(contactId) {
    readCookie();

    // Ask for confirmation before editing
    if (!confirm("Are you sure you want to save these changes to this contact?")) {
        return; 
    }

    let firstName = document.getElementById("firstName").value;
    let lastName = document.getElementById("lastName").value;
    let email = document.getElementById("email").value;
    let phone = document.getElementById("phone").value;

    document.getElementById("contactAddResult").innerHTML = "";

    let tmp = {
        contactId: contactId,
        userId: userId,
        firstName: firstName,
        lastName: lastName,
        email: email,
        phone: phone
    };

    let jsonPayload = JSON.stringify(tmp);
    let url = urlBase + '/EditContact.' + extension;

    let xhr = new XMLHttpRequest();
    xhr.open("POST", url, true);
    xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");

    try {
        xhr.onreadystatechange = function () {
            if (this.readyState == 4 && this.status == 200) {
                try {
                    let jsonObject = JSON.parse(xhr.responseText);

                    if (!jsonObject.success) {
                        document.getElementById("contactAddResult").innerHTML = jsonObject.error;
                    } else {
                        document.getElementById("contactAddResult").innerHTML = "Contact updated successfully";

                        // Clear form fields
                        document.getElementById("firstName").value = "";
                        document.getElementById("lastName").value = "";
                        document.getElementById("email").value = "";
                        document.getElementById("phone").value = "";

                        // Refresh table
                        loadContacts();
                    }
                } catch (err) {
                    document.getElementById("contactAddResult").innerHTML = err.message;
                }
            }
        };

        xhr.send(jsonPayload);
    } catch (err) {
        document.getElementById("contactAddResult").innerHTML = err.message;
    }
}

function searchContacts() {
    const searchValue = document.getElementById("searchText").value.toUpperCase().trim();
    const selections = searchValue.split(' ').filter(s => s); // split into words, remove empty
    const table = document.getElementById("contacts");
    const tbody = table.tBodies[0];
    const tr = tbody.getElementsByTagName("tr");

    let anyVisible = false;

    for (let i = 0; i < tr.length; i++) {
        const tds = tr[i].getElementsByTagName("td");
        let rowText = "";

        // Concatenate all cells except last column (actions)
        for (let j = 0; j < tds.length; j++) {
            rowText += tds[j].textContent.toUpperCase() + " ";
        }

        // Determine if row matches search
        let show = selections.length === 0 || selections.some(sel => rowText.indexOf(sel) > -1);

        tr[i].style.display = show ? "" : "none";

        if (show) anyVisible = true;
    }

    // Optional: show a "No results" row
    let noResultRow = document.getElementById("noResultRow");
    if (!anyVisible && selections.length > 0) {
        if (!noResultRow) {
            noResultRow = tbody.insertRow(0);
            noResultRow.id = "noResultRow";
            const cell = noResultRow.insertCell(0);
            cell.colSpan = table.tHead.rows[0].cells.length;
            cell.style.textAlign = "center";
            cell.style.fontStyle = "italic";
            cell.style.color = "#555";
            cell.innerText = "No matching contacts found";
			tr[i].style.display = show ? "" : "none";
        }
    } else if (noResultRow) {
        noResultRow.remove();
    }
}

function loadContacts() {
    let tmp = { search: "", userId: userId };
    let jsonPayload = JSON.stringify(tmp);

    let url = urlBase + '/SearchContacts.' + extension;

    let xhr = new XMLHttpRequest();
    xhr.open("POST", url, true);
    xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");
    try {
        xhr.onreadystatechange = function () {
            if (this.readyState == 4 && this.status == 200) {
                let jsonObject = JSON.parse(xhr.responseText);

                let tbody = document.getElementById("tbody");
                tbody.innerHTML = "";

                if (jsonObject.results && jsonObject.results.length > 0) {
                    for (let i = 0; i < jsonObject.results.length; i++) {
                        const contact = jsonObject.results[i];

                        let row = tbody.insertRow(i);
                        row.insertCell(0).innerText = contact.firstName;
                        row.insertCell(1).innerText = contact.lastName;
                        row.insertCell(2).innerText = contact.email;
                        row.insertCell(3).innerText = contact.phone;

                        // Actions column
                        let actionsCell = row.insertCell(4);

                        // Edit button
                        let editBtn = document.createElement("button");
                        editBtn.innerHTML = '<i class="fas fa-pencil-alt"></i>';
                        editBtn.className = "table-btn edit-btn";
                        editBtn.onclick = function () {
                            document.getElementById("firstName").value = contact.firstName;
                            document.getElementById("lastName").value = contact.lastName;
                            document.getElementById("email").value = contact.email;
                            document.getElementById("phone").value = contact.phone;
                            toggleAddContactForm();

                            document.getElementById("submitContactButton").onclick = function () {
                                editContact(contact.id);  // Changed to id
                            }
                        };

                        // Delete button
                        let deleteBtn = document.createElement("button");
                        deleteBtn.innerHTML = '<i class="fas fa-times"></i>';
                        deleteBtn.className = "table-btn delete-btn";
                        deleteBtn.onclick = function () {
                            deleteContact(contact.id);  // Changed to id
                        };

                        let btnContainer = document.createElement("div");  // create a wrapper div for the buttons
                        btnContainer.className = "table-btn-container"; 
                        actionsCell.appendChild(editBtn);
                        actionsCell.appendChild(deleteBtn);
                    }
                }
            }
        };
        xhr.send(jsonPayload);
    } catch (err) {
        console.error("Error loading contacts: " + err.message);
    }
}

function showTable() {
	loadContacts();
}