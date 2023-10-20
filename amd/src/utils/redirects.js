import {changeGradesUrl} from "./userMenu";
import {changeProfileReportUrls} from "./pageCustomizer";

export const redirects = (params = []) => {
    const redirectsMap = {
        'profile': {
            'reportsGrades': changeProfileReportUrls
        },
        'userMenu': {
            'gradesStudent': changeGradesUrl
        },
        'default': () => false
    };

    params.forEach(({page, url, key}) => {
        if (redirectsMap[page] && redirectsMap[page][key]) {
            redirectsMap[page][key](url);
        } else {
            redirectsMap.default(url);
        }
    });
};
