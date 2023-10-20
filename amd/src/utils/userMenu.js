export const customizeUserMenu = (role = 'student', urlSegments = []) => {
    const userActionMenuItems = document.querySelectorAll('#user-action-menu-items__anchor');
    const urlsToRemove = ['reportbuilder/index.php'];
    const itemsToRemove = urlSegments.length && role === 'all' ? urlSegments : urlsToRemove;

    userActionMenuItems.forEach(item => {
        itemsToRemove.forEach(itemToRemove => {
            // eslint-disable-next-line babel/no-unused-expressions
            item.href.includes(itemToRemove) && item?.remove();
        });
    });
};

export const changeGradesUrl = (url = '') => {
    const isStudent = document.querySelector('body').classList.contains('role-student');

    if (isStudent) {
        const userActionMenuItems = document.querySelectorAll('#user-action-menu-items__anchor');
        userActionMenuItems.forEach(item => {
            if (item.href.includes('grade/report/overview')) {
                item.href = url;
                item.setAttribute("target", "_blank");
            }
        });
    }
};
