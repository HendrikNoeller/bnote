sap.ui.jsview("bnote.rehearsal", {
	
	buttonBar: null,
	
	getControllerName: function() {
		return "bnote.rehearsal";
	},
    
	setButtons: function(participate){
		if(this.buttonBar != null) {
			var bid = "";
			switch(participate) {
			case -1: bid = "rehearsalNoBtn"; break;
			case 0: bid = "rehearsalMaybeBtn"; break;
			default: bid = "rehearsalOkBtn";
			}
			this.buttonBar.setSelectedButton(bid);
		}
	},
	
    createContent: function(oController) {
		var rehearsalForm = new sap.ui.layout.form.SimpleForm({
            title: "Probendetails",
            content: [
                // begin
                new sap.m.Label({text: "Probenbeginn"}),
                new sap.m.Text({text: "{begin}"}),
                // end
                new sap.m.Label({text: "Probenende"}),
                new sap.m.Text({text: "{end}"}),
                // location
                new sap.m.Label({text: "Ort"}),
                new sap.m.Text({text: "{name}"}),
                // notes
                new sap.m.Label({text: "Anmerkungen"}),
                new sap.m.Text({text: "{notes}"}),
            ]
        });
        
        this.buttonBar = new sap.m.SegmentedButton({
            width: "100%",
            buttons: [
               new sap.m.Button("rehearsalOkBtn", {
            	   text: "OK",            	  
            	   //press: oController.onOkPress
            	   }),
               new sap.m.Button("rehearsalMaybeBtn",{
            	   text: "vielleicht",
            	
            	   //press: onMaybePress,
            	   
            	   }),
               new sap.m.Button("rehearsalNoBtn",{
            	   text: "Kann nicht",
            	  
            	   //press: onNoPress
            	   })
            ]
        });
        
		
		var page = new sap.m.Page("RehearsalPage", {
            title: "Probe",
            showNavButton: true,
            navButtonPress: function() {
                app.back();
            },
			content: [ rehearsalForm, this.buttonBar ]
		});
		
		return page;
	}
    
});