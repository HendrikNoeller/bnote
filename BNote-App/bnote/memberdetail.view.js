sap.ui.jsview("bnote.memberdetail", {
	
	getControllerName: function() {
		return "bnote.memberdetail";
	},
	
	setDataVisibility: function (dataVisibility){
		
	if (dataVisibility[0] == ""){	// dataVisibility[0] == phonenummber
			this.phoneButton.setVisible(false);
		}
	else{
			this.phoneButton.setVisible(true);
		}
	if (dataVisibility[1] == ""){	// dataVisibility[1] == mobilenummber
			this.mobileButton.setVisible(false);
		}
	else{
			this.mobileButton.setVisible(true);
		}
	if (dataVisibility[2] == ""){	// dataVisibility[2] == emailadress
			this.emailButton.setVisible(false);
		}
	else{
			this.emailButton.setVisible(true);
		}
	},
	
	createContent: function(){
	         
		   jQuery.sap.require("sap.ui.core.IconPool");
		var memberdetailsForm = new sap.ui.layout.form.SimpleForm({
            title: "Kontaktdaten",
            content: [
                new sap.m.Label({text: "Name"}),
                new sap.m.Text({text: "{fullname}"}),  
                
                new sap.m.Label({text: "Instrument"}),
                new sap.m.Text({text: "{instrument}"}),
                
                new sap.m.Label({text: "Adresse"}),
                new sap.m.Text({text: "{street}"}),
                new sap.m.Text({text: "{city}"}),
           
                new sap.m.Label({text: "Telefon"}),
                this.phoneButton = new sap.m.Button({
                	text: "{phone}",
                	width: "100%",
                	icon: sap.ui.core.IconPool.getIconURI( "phone" ),
                }),
               
                new sap.m.Label({text: "Handy"}),
                this.mobileButton = new sap.m.Button({
                	text: "{mobile}",
                	width: "100%",
                	icon: sap.ui.core.IconPool.getIconURI( "iphone-2" ),
                }),
                
                new sap.m.Label({text: "Email"}),
                this.emailButton = new sap.m.Button({
                	text: "{email}",
                	width: "100%",
                	icon: sap.ui.core.IconPool.getIconURI( "email" ),
                }),
            ]
        });
		
	var page = new sap.m.Page("memberdetailPage", {
        title: "Kontaktdaten",
        showNavButton: true,
        navButtonPress: function() {
            app.back();
        },
		content: [ memberdetailsForm ],
        footer: [  ]
	});
	return page;
	}
	
});