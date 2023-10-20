export const customizePreferencesPage = (role = 'student', urlSegments = []) => {
    if (currentPathHasCriteria('preferences')) {
        const preferencesGroupsUrl = document.querySelectorAll('.preferences-groups__anchor-url');
        const urlsToRemove = ['change_password', 'editor.php', 'contentbank.php', 'notificationpreferences.php'];
        const itemsToRemove = urlSegments.length && role === 'all' ? urlSegments : urlsToRemove;

        preferencesGroupsUrl.forEach(preferenceOption => {
            itemsToRemove.forEach(itemToRemove => {
                // eslint-disable-next-line babel/no-unused-expressions
                preferenceOption.href.includes(itemToRemove) &&
                preferenceOption.closest('.preferences-groups__anchor-container').remove();
            });
        });
    }
};

export const customizeForumSettingsPage = (role = 'student', urlSegments = []) => {
    if (currentPathHasCriteria('user/forum.php?id')) {
        const segmentsToRemove = ['id_useexperimentalui_label', 'id_trackforums_label'];
        const itemsToRemove = urlSegments.length && role === 'all' ? urlSegments : segmentsToRemove;

        const forumSettingLabelElements = document.querySelectorAll('.option-label__label');

        forumSettingLabelElements.forEach(forumLabel => {
            itemsToRemove.forEach(itemToRemove => {
                // eslint-disable-next-line babel/no-unused-expressions
                forumLabel.id.includes(itemToRemove) &&
                forumLabel.closest('.option-label__container').remove();
            });
        });
    }
};

export const changeProfileReportUrls = (url = '') => {
    if (!isStudent() || !currentPathHasCriteria('profile')) {
        return;
    }

    const nodeCategories = document.querySelectorAll('.node_category');
    nodeCategories.forEach(nodeCategory => {
        const reportsLinks = nodeCategory.querySelectorAll('a');

        reportsLinks.forEach(link => {
            if (link.href.includes('grade/report/overview')) {
                link.href = url;
                link.setAttribute('target', '_blank');
            }
        });
    });
};

const currentPathHasCriteria = (criteria) => window.location.href.includes(criteria);
const isStudent = () => document.querySelector('body').classList.contains('role-student');
