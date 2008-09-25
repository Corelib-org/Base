var DatabaseTool = {
	dependencies: new Array(),
	objects: new Array(),
	descstate: false,
	
	showAllDescriptions: function(state){
		desclist = document.getElementsByClassName('DatabaseToolHiddenBlock');
		for (var i = 0; i <= (desclist.length - 1); i++) {
			if (state === false) {
				desclist[i].style.display = 'none';
			} else {
				desclist[i].style.display = 'block';
			}
		}
	},
	
	selectAllObjects: function(state){
		for (var i = (this.objects.length - 1); i >= 0; i--) {
			if (state === false) {
				$('checkbox_v_' + this.objects[i]).checked = false;
			} else {
				$('checkbox_v_' + this.objects[i]).checked = true;
			}
			this.toggleExcludeAction(this.objects[i], $('checkbox_v_' + this.objects[i]), $('exclude_' + this.objects[i]));
		}
	},
	
	addObject: function(object, dependencies){
		cleaned = new Array();
		for (var i = 0; i <= (dependencies.length - 1); i++) {
			if(!this.inArray(cleaned, dependencies[i]) && dependencies[i] != object){
				cleaned.push(dependencies[i]);
			}
		}
		this.dependencies.push(cleaned);
		this.objects.push(object);
	},
	
	inArray: function(array, element){
		for (var i = 0; i <= (array.length - 1); i++) {
			if(element == array[i]){
				return true;
			}
		}
		return false;
	},
	
	toggleExcludeAction: function(object, state, element){
		if(state.checked == true){
			element.checked = false;
		} else {
			element.checked = true;
		}
		this._checkDependencies(object, !element.checked);
	},
	
	toggleUpdateDescription: function(element){
		if(element.style.display == 'none'){
			element.style.display = 'block';
		} else {
			element.style.display = 'none';
		}
	},
	
	_checkDependencies: function(object, state){
		var objectID = this._getObjectID(object)
		if(objectID !== false){
			for (var i = 0; i <= (this.dependencies[objectID].length - 1); i++) {
				var iID = this._getObjectID(this.dependencies[objectID][i]);
				if (iID !== false) {
					if (this._checkOtherDependencies(object, this.objects[iID])) {
						if (this.objects[iID] != object) {
							$('checkbox_v_' + this.objects[iID]).disabled = state;
						}
						if (state) {
							$('checkbox_v_' + this.objects[iID]).checked = true;
						}
					} else {
						if (this.objects[iID] != object) {
							$('checkbox_v_' + this.objects[iID]).disabled = true;
						}
						$('checkbox_v_' + this.objects[iID]).checked = true;
					}
					if (this.objects[iID] != object) {
						this.toggleExcludeAction(this.objects[iID], $('checkbox_v_' + this.objects[iID]), $('exclude_' + this.objects[iID]));
					}
				}
			}
		}
	},
	
	_getObjectID: function(object){
		for(var i = 0; i <= (this.objects.length - 1); i++){
			if(this.objects[i] == object){
				return i;
			}
		}
		return false;
	},
	
	_checkOtherDependencies: function(parent, object){
		for (var i = 0; i <= (this.dependencies.length - 1); i++) {
			for (var di = 0; di <= (this.dependencies[i].length - 1); di++) {
				if(this.dependencies[i][di] == object && $('checkbox_v_' + this.objects[i]).checked == true){
					if (parent != this.dependencies[i][di]) {
						return false;
					}
				}
			}
		}
		return true;
	},
}