export const addNote = ({ dispatch }) => {
  dispatch('ADD_NOTE')
}
export const editNote = ({ dispatch }, e) => {
  dispatch('EDIT_NOTE', e.target.value)
}
export const deleteNote = ({ dispatch }) => {
  dispatch('DELETE_NOTE')
}
export const editaccess = ({ dispatch }, e) => {
  dispatch('EDIT_Access', e.target.value)
}
export const edittime = ({ dispatch }, e) => {
  dispatch('EDIT_Time', e.target.value)
}
export const updateActiveNote = ({ dispatch }, note) => {
  dispatch('SET_ACTIVE_NOTE', note)
}

export const toggleFavorite = ({ dispatch }) => {
  dispatch('TOGGLE_FAVORITE')
}
